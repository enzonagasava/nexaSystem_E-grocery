<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\IntegracaoPagamento;


class PaymentController extends Controller
{
 public function index()
    {
        $cart = session()->get('cart', []);

        $integracao = IntegracaoPagamento::query()->first();

        if (!$integracao || !$integracao->public_key) {
            abort(500, 'MercadoPago não configurado');
        }

        return Inertia::render('PagePay', [
            'cart' => $cart,
            'mpPublicKey' => $integracao->public_key,
        ]);
    }
}
