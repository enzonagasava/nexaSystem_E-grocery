<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Produto;
use App\Models\GerenciarPedido;
use App\Models\Cliente;
use App\Models\Pedido;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
         $user = Auth::user(); 

         // Obter períodos dos filtros
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
                'imagens' => $product->imagens
            ];
        });

        // Dados para o gráfico de valor vendido
        $valorVendido = GerenciarPedido::where('status', 'finalizado')
            ->where('created_at', '>=', Carbon::now()->subDays($periodoValor))
            ->selectRaw('DATE(created_at) as data, SUM(valor) as total')
            ->groupBy('data')
            ->orderBy('data')
            ->get()
            ->map(fn($item) => [
                'data' => Carbon::parse($item->data)->format('d/m'),
                'valor' => (float) $item->total
            ]);

        // Dados para o gráfico de produtos vendidos
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
                'quantidade' => (int) $item->quantidade
            ]);

        // Dados para o gráfico de clientes
        if ($periodoClientes >= 365) {
            // Por mês no ano atual
            $clientesPorMes = Cliente::selectRaw('MONTH(created_at) as mes, COUNT(*) as total')
                ->whereYear('created_at', Carbon::now()->year)
                ->groupBy('mes')
                ->orderBy('mes')
                ->get()
                ->map(fn($item) => [
                    'label' => (int) $item->mes,
                    'total' => (int) $item->total,
                    'tipo' => 'mes'
                ]);
        } else {
            // Por dia no período selecionado
            $clientesPorMes = Cliente::selectRaw('DATE(created_at) as data, COUNT(*) as total')
                ->where('created_at', '>=', Carbon::now()->subDays($periodoClientes))
                ->groupBy('data')
                ->orderBy('data')
                ->get()
                ->map(fn($item) => [
                    'label' => Carbon::parse($item->data)->format('d/m'),
                    'total' => (int) $item->total,
                    'tipo' => 'dia'
                ]);
        }

        // Dados para o gráfico de entregas
        $entregasPorDia = GerenciarPedido::where('status', 'finalizado')
            ->where('updated_at', '>=', Carbon::now()->subDays($periodoEntregas))
            ->selectRaw('DATE(updated_at) as data, COUNT(*) as total')
            ->groupBy('data')
            ->orderBy('data')
            ->get()
            ->map(fn($item) => [
                'data' => Carbon::parse($item->data)->format('d/m'),
                'total' => (int) $item->total
            ]);


            $driver = DB::getDriverName();

            $groupConcat = $driver === 'sqlite'
                ? 'GROUP_CONCAT(produtos.nome, ", ")'
                : 'GROUP_CONCAT(produtos.nome SEPARATOR ", ")';

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
                    'cliente'  => $item->cliente,
                    'produtos' => $item->produtos,
                    'status'   => ucwords(str_replace('-', ' ', $item->status)),
                    'valor'    => (float) $item->valor_total,
                    'itens'    => (int) $item->itens,
                    'tempo'    => Carbon::parse($item->data)->diffForHumans(null, true),
                    'data'     => Carbon::parse($item->data)->format("d/m/Y H:i"),
                ]);



        return Inertia::render('admin/Dashboard', [
            'user' => $user,
            'products' => $products,
            'dashboardData' => [
                'valorVendido' => $valorVendido,
                'produtosVendidos' => $produtosVendidos,
                'clientesPorMes' => $clientesPorMes,
                'entregasPorDia' => $entregasPorDia,
                'historicoCompras' => $historicoCompras
            ],
            'periodoValor' => (string) $periodoValor,
            'periodoEntregas' => (string) $periodoEntregas,
            'periodoProdutos' => (string) $periodoProdutos,
            'periodoClientes' => (string) $periodoClientes
        ]);
    }
}
