<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\GerenciarPedido;
use App\Models\Plataforma;
use Inertia\Inertia;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pedidos\PedidosStoreRequest;
use App\Models\Pedido;
use App\Models\Produto;


class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $statusFiltro = $request->input('status', 'todos');
        
        $statusValido = in_array($statusFiltro, ['todos', 'em-andamento', 'a-caminho', 'finalizado']) 
            ? $statusFiltro 
            : 'todos';

        $pedidos = GerenciarPedido::with([
            'cliente:id,nome',
            'plataforma:id,nome',
            'pedidos.produto:id,nome',
        ])->get()
        ->map(function ($pedido) {
            return [
                'id' => $pedido->id,
                'cod_pedido' => $pedido->cod_pedido,
                'cliente' => $pedido->cliente?->nome ?? 'Sem cliente',
                'endereco' => $pedido->endereco ?? '-',
                'plataforma' => $pedido->plataforma?->nome ?? '-',
                'produtos' => $pedido->CodPedidos->pluck('produto.nome')->filter()->values(),
                'valor' => number_format($pedido->valor, 2, ',', '.'),
                'status' => $pedido->status,
                'created_at_formatted' => optional($pedido->created_at)->format('d/m/Y H:i'),
            ];
        });

        return Inertia::render('admin/ecommerce/pedidos/Pedidos', [
            'pedidos' => $pedidos,
            'statusFiltro' => $statusValido,
        ]);
    }


  public function create()
  {
        $plataformas = Plataforma::orderBy('nome')->get(['id', 'nome']);

        return Inertia::render('admin/ecommerce/pedidos/AdicionarPedido', [
            'plataformas' => $plataformas,
        ]);
  }

  public function store(PedidosStoreRequest $request)
  {
      $data = $request->validated();

      $codigoPedido = 'PED-' . strtoupper(Str::random(8));

      foreach ($data['produtos'] as $produto) {
          $produtoModel = Produto::findOrFail($produto['id']);

          if ($produtoModel->estoque < $produto['quantidade']) {
              return back()->withErrors([
                  'estoque' => "O produto {$produtoModel->nome} não possui estoque suficiente."
              ]);
          }

        $produtoModel->estoque -= $produto['quantidade'];
        $produtoModel->save();

          Pedido::create([
              'produto_id' => $produto['id'],
              'quantidade' => $produto['quantidade'],
              'valor_pedido' => $produto['valor'],
              'cod_pedido' => $codigoPedido,
          ]);
      }

      GerenciarPedido::create([
          'cliente_id'  => $data['clienteSelecionado']['id'],
          'cod_pedido'  => $codigoPedido,
          'valor'       => $data['valorTotal'],
          'endereco'    => $data['endereco'] ?? null,
          'status'      => $data['status'],
          'plataforma_id'  => $data['plataformaSelecionada'] ?? null,
      ]);


      return Inertia::render('admin/ecommerce/pedidos/Pedidos', [
          'status' => $data['status'],
          'message' => 'Pedido adicionado com sucesso'
      ]);

  }

  public function edit($id){
      $pedido = GerenciarPedido::with([
          'cliente:id,nome,email',
          'CodPedidos.produto:id,nome',
      ])->findOrFail($id);

      $plataformas = Plataforma::orderBy('nome')->get(['id', 'nome']);


      return Inertia::render('admin/ecommerce/pedidos/EditarPedido', [
          'pedidoEditavel' => $pedido,
          'plataformas' => $plataformas
      ]);
  }

  public function update(PedidosStoreRequest $request, $id)
  {
      $pedido = GerenciarPedido::findOrFail($id);

      $data = $request->validated();

      $pedido->CodPedidos()->delete();

      foreach ($request->produtos as $p) {
        $produtoModel = Produto::findOrFail($p['id']);

        if ($produtoModel->estoque < $p['quantidade']) {
            return back()->withErrors([
                'estoque' => "O produto {$produtoModel->nome} não possui estoque suficiente."
            ]);
        }

        $produtoModel->estoque -= $p['quantidade'];
        $produtoModel->save();

        $pedido->CodPedidos()->create([
            'produto_id' => $p['id'],
            'quantidade' => $p['quantidade'],
            'valor_pedido' => $p['valor'],
            'cod_pedido' => $pedido->cod_pedido,
        ]);
      }

      $pedido->update([
          'cliente_id'  => $data['clienteSelecionado']['id'],
          'cod_pedido'  => $pedido->cod_pedido,
          'valor'       => $data['valorTotal'],
          'endereco'    => $data['endereco'] ?? null,
          'status'      => $data['status'],
          'plataforma_id'  => $data['plataformaSelecionada'] ?? null,
      ]);


      return Inertia::render('admin/ecommerce/pedidos/Pedidos', [
            'message' => 'Pedido Atualizado com Sucesso'
        ]);
  }

  public function view($id){
    $pedido = GerenciarPedido::with([
        'cliente:id,nome,email',
        'CodPedidos.produto:id,nome',
    ])->findOrFail($id);

    $plataformas = Plataforma::orderBy('nome')->get(['id', 'nome']);


    return Inertia::render('admin/ecommerce/pedidos/VisualizarPedido', [
        'pedido' => $pedido,
        'plataformas' => $plataformas
    ]);
  }

  public function avancarStatus($id)
  {
      $pedido = GerenciarPedido::findOrFail($id);

      $etapas = ['em-andamento', 'a-caminho', 'finalizado'];

      $atual = array_search($pedido->status, $etapas);

      if ($atual !== false && $atual < count($etapas) - 1) {
          $pedido->status = $etapas[$atual + 1];
          $pedido->save();
      }

      return Inertia::render('admin/ecommerce/pedidos/Pedidos', [
            'message' => 'Pedido Atualizado com Sucesso'
      ]);
  }


}
