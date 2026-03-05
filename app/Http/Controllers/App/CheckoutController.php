<?php

namespace App\Http\Controllers\App;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Exceptions\MPApiException;
use App\Models\GerenciarPedido;
use App\Models\IntegracaoPagamento;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Pedido;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Payment\PaymentClient;
use App\Services\MercadoPagoService;



class CheckoutController extends Controller
{
    public function process(Request $request)
    {
        try {

            $integracao = IntegracaoPagamento::first();

            if (
                !$integracao ||
                !$integracao->public_key ||
                !$integracao->access_token
            ) {
                return response()->json([
                    'error' => 'Credenciais Mercado Pago inválidas'
                ], 500);
            }

            MercadoPagoConfig::setAccessToken($integracao->access_token);

            $client = new PreferenceClient();

            $preference = $client->create([
                "items" => collect($request->items)->map(fn ($item) => [
                    "id" => $item['id'] ?? uniqid(),
                    "title" => $item['title'],
                    "quantity" => $item['quantity'],
                    "unit_price" => (float) $item['unit_price'],
                    "currency_id" => "BRL",
                ])->toArray(),

                "payer" => [
                    "name"  => $request->name,
                    "email" => $request->email,
                    "phone" => [
                        "area_code" => "11",
                        "number" => $request->phone
                    ],
                    "address" => [
                        "street_name" => $request->street_name,
                    ],
                ],
                "metadata" => [
                    "customer_name"  => $request->name,
                    "customer_email" => $request->email,
                    "price"          => $request->price,
                ],

                "payment_methods" => [
                    "installments" => 12,
                ],
                "notification_url" => route('checkout.webhook'),

                "back_urls" => [
                    "success" => route('checkout.sucesso'),
                    "failure" => route('checkout.falha'),
                    "pending" => route('checkout.pendentes'),
                ]
            ]);


             $initPoint = $preference->sandbox_init_point ?? $preference->init_point;
        
        // Garantir que é sandbox (se estiver testando)
        if (str_contains($integracao->access_token, 'TEST-') && 
            !str_contains($initPoint, 'sandbox.')) {
            $initPoint = str_replace(
                'mercadopago.com.br',
                'sandbox.mercadopago.com.br',
                $initPoint
            );
        }


            return response()->json([
                "preference_id" => $preference->id,
                "init_point" => $initPoint,
            ]);


        } catch (\Throwable $e) {

            Log::error('Erro checkout', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Erro ao processar pagamento',
            ], 500);
        }
    }

   public function webhook(Request $request)
    {
        try {
            Log::info("Webhook recebido", $request->all());

            $integracao = IntegracaoPagamento::firstOrFail();
            new MercadoPagoService($integracao);

            Log::info("Webhook recebido", $request->all());

            $topic = $request->get('type') ?? $request->get('topic');

            if ($topic !== 'payment') {
                return response()->json(['status' => 'ignored'], 200);
            }

            $paymentId = data_get($request->get('data'), 'id');

            if (!$paymentId) {
                return response()->json(['error' => 'invalid payment id'], 400);
            }

            $client  = new PaymentClient();
            $payment = $client->get($paymentId);

            if ($payment->status !== 'approved') {
                return response()->json(['status' => 'not approved'], 200);
            }

            $meta = (array) $payment->metadata;

            // =================================
            // 1️⃣ Verificar se já processamos esse pagamento
            // =================================
            if (GerenciarPedido::where('payment_id', $paymentId)->exists()) {
                Log::warning("Webhook duplicado detectado", ['payment_id' => $paymentId]);
                return response()->json(['status' => 'already processed'], 200);
            }

            // =================================
            // 2️⃣ Criar cliente
            // =================================
            $cliente = Cliente::firstOrCreate(
                ['email' => $meta['customer_email']],
                [
                    'nome' => $meta['customer_name'],
                    'numero' => $meta['customer_number'] ?? null,
                ]
            );

            // =================================
            // 3️⃣ Criar GERENCIAR PEDIDO
            // =================================
            $codigoPedido = uniqid('PED-');

            $gerenciar = GerenciarPedido::create([
                'cliente_id' => $cliente->id,
                'cod_pedido' => $codigoPedido,
                'valor' => $meta['price'],
                'endereco' => $meta['customer_address'] ?? null,
                'status' => 'approved',
                'payment_id' => $paymentId,
                'plataforma_id'=> 1,
            ]);

            // =================================
            // 4️⃣ Criar Pedido(s) + Subtrair Estoque
            // =================================

            if (!empty($payment->additional_info->items)) {

                foreach ($payment->additional_info->items as $item) {

                    $produtoId = $item->id ?? null;

                    if ($produtoId) {
                        $produto = Produto::find($produtoId);

                        if ($produto) {

                            // Verificar estoque
                            if ($produto->estoque < $item->quantity) {
                                Log::error("Sem estoque suficiente", [
                                    'produto' => $produto->nome,
                                    'estoque' => $produto->estoque,
                                    'required' => $item->quantity
                                ]);

                                continue; // opcional: pode abortar o pedido
                            }

                            // Subtrai estoque
                            $produto->estoque -= $item->quantity;
                            $produto->save();
                        }
                    }

                    Pedido::create([
                        'produto_id' => $produtoId,
                        'quantidade' => $item->quantity,
                        'valor_pedido' => $item->unit_price,
                        'cod_pedido' => $codigoPedido,
                    ]);
                }
            }

            Log::info("Pedido criado via webhook", [
                'cod_pedido' => $codigoPedido,
                'cliente' => $cliente->email,
            ]);

            return response()->json(['status' => 'Pedido criado'], 200);

         } catch (\Throwable $e) {

        Log::error("Erro webhook", [
            "msg" => $e->getMessage(),
        ]);

        return response()->json(['error' => 'Webhook error'], 500);
        } 
    }

}
