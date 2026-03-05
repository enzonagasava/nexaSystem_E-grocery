<?php

namespace App\Http\Controllers\Admin\Ecommerce;

use App\Http\Controllers\Controller;
<<<<<<< HEAD
use App\Models\Cliente;
use App\Models\GerenciarPedido;
use App\Models\Pedido;
use App\Models\Produto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
=======
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
<<<<<<< HEAD
     * Exibe o dashboard do módulo e-commerce com dados e gráficos.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();

        $periodoValor = (int) $request->input('periodoValor', 30);
        $periodoEntregas = (int) $request->input('periodoEntregas', 30);
        $periodoProdutos = (int) $request->input('periodoProdutos', 30);
        $periodoClientes = (int) $request->input('periodoClientes', 30);

        $products = Produto::with('imagens')->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'nome' => $product->nome,
                'descricao' => $product->descricao,
                'estoque' => $product->estoque,
                'tamanhos' => $product->tamanhos,
                'imageUrl' => $product->imagens->first()
                    ? asset('storage/' . $product->imagens->first()->imagem_path)
                    : null,
                'created_at' => $product->created_at
                    ? $product->created_at->format('d/m/Y H:i')
                    : null,
                'imagens' => $product->imagens,
            ];
        });

        $valorVendido = GerenciarPedido::where('status', 'finalizado')
            ->where('created_at', '>=', Carbon::now()->subDays($periodoValor))
            ->selectRaw('DATE(created_at) as data, SUM(valor) as total')
            ->groupBy('data')
            ->orderBy('data')
            ->get()
            ->map(fn($item) => [
                'data' => Carbon::parse($item->data)->format('d/m'),
                'valor' => (float) $item->total,
            ]);

        $produtosVendidos = Pedido::join('produtos', 'pedidos.produto_id', '=', 'produtos.id')
            ->join('gerenciar_pedidos', 'pedidos.cod_pedido', '=', 'gerenciar_pedidos.cod_pedido')
            ->where('gerenciar_pedidos.status', 'finalizado')
            ->where('gerenciar_pedidos.created_at', '>=', Carbon::now()->subDays($periodoProdutos))
            ->selectRaw('produtos.nome, SUM(pedidos.quantidade) as quantidade')
            ->groupBy('produtos.id', 'produtos.nome')
            ->orderByDesc('quantidade')
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'nome' => $item->nome,
                'quantidade' => (int) $item->quantidade,
            ]);

        if ($periodoClientes >= 365) {
            $clientesPorMes = Cliente::selectRaw('MONTH(created_at) as mes, COUNT(*) as total')
                ->whereYear('created_at', Carbon::now()->year)
                ->groupBy('mes')
                ->orderBy('mes')
                ->get()
                ->map(fn($item) => [
                    'label' => (int) $item->mes,
                    'total' => (int) $item->total,
                    'tipo' => 'mes',
                ]);
        } else {
            $clientesPorMes = Cliente::selectRaw('DATE(created_at) as data, COUNT(*) as total')
                ->where('created_at', '>=', Carbon::now()->subDays($periodoClientes))
                ->groupBy('data')
                ->orderBy('data')
                ->get()
                ->map(fn($item) => [
                    'label' => Carbon::parse($item->data)->format('d/m'),
                    'total' => (int) $item->total,
                    'tipo' => 'dia',
                ]);
        }

        $entregasPorDia = GerenciarPedido::where('status', 'finalizado')
            ->where('updated_at', '>=', Carbon::now()->subDays($periodoEntregas))
            ->selectRaw('DATE(updated_at) as data, COUNT(*) as total')
            ->groupBy('data')
            ->orderBy('data')
            ->get()
            ->map(fn($item) => [
                'data' => Carbon::parse($item->data)->format('d/m'),
                'total' => (int) $item->total,
            ]);

        $driver = DB::getDriverName();
        $groupConcat = $driver === 'sqlite'
            ? 'STRING_AGG(produtos.nome, ", ")'
            : "STRING_AGG(produtos.nome::text, ', ')";

        $historicoCompras = Pedido::join('produtos', 'pedidos.produto_id', '=', 'produtos.id')
            ->join('gerenciar_pedidos', 'pedidos.cod_pedido', '=', 'gerenciar_pedidos.cod_pedido')
            ->join('clientes', 'gerenciar_pedidos.cliente_id', '=', 'clientes.id')
            ->selectRaw("
                clientes.nome AS cliente,
                $groupConcat AS produtos,
                gerenciar_pedidos.valor AS valor_total,
                gerenciar_pedidos.status AS status,
                gerenciar_pedidos.created_at AS data,
                gerenciar_pedidos.cod_pedido,
                COUNT(pedidos.id) AS itens
            ")
            ->groupBy(
                'gerenciar_pedidos.cod_pedido',
                'clientes.nome',
                'gerenciar_pedidos.valor',
                'gerenciar_pedidos.status',
                'gerenciar_pedidos.created_at'
            )
            ->orderByDesc('gerenciar_pedidos.created_at')
            ->paginate(10)
            ->through(fn($item) => [
                'cliente' => $item->cliente,
                'produtos' => $item->produtos,
                'status' => ucwords(str_replace('-', ' ', $item->status)),
                'valor' => (float) $item->valor_total,
                'itens' => (int) $item->itens,
                'tempo' => Carbon::parse($item->data)->diffForHumans(null, true),
                'data' => Carbon::parse($item->data)->format('d/m/Y H:i'),
            ]);

        return Inertia::render('admin/ecommerce/Dashboard', [
            'modulo' => 'ecommerce',
            'user' => $user,
            'products' => $products,
            'dashboardData' => [
                'valorVendido' => $valorVendido,
                'produtosVendidos' => $produtosVendidos,
                'clientesPorMes' => $clientesPorMes,
                'entregasPorDia' => $entregasPorDia,
                'historicoCompras' => $historicoCompras,
            ],
            'periodoValor' => (string) $periodoValor,
            'periodoEntregas' => (string) $periodoEntregas,
            'periodoProdutos' => (string) $periodoProdutos,
            'periodoClientes' => (string) $periodoClientes,
=======
     * Exibe o dashboard do módulo e-commerce.
     */
    public function index(): Response
    {
        return Inertia::render('admin/ecommerce/Dashboard', [
            'modulo' => 'ecommerce',
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
        ]);
    }
}
