<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Paciente;

class SearchController extends Controller
{
  public function buscarCliente(Request $request)
      {
          $search = trim($request->query('search', ''));

          if ($search === '') {
              return response()->json([]);
          }

          $key = 'clientes_busca_' . md5($search);

          return Cache::remember($key, now()->addSeconds(15), function () use ($search) {
              $clientes = Cliente::select('id', 'nome', 'email')
                  ->whereRaw("MATCH(nome, email) AGAINST(? IN BOOLEAN MODE)", [$search . '*'])
                  ->limit(8)
                  ->get();

              if ($clientes->isEmpty()) {
                  $clientes = Cliente::select('id', 'nome', 'email')
                      ->where('nome', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->limit(8)
                      ->get();
              }

              return $clientes;
          });
      }

      public function buscarProduto(Request $request)
      {
          $search = trim($request->query('search', ''));

          if ($search === '') {
              return response()->json([]);
          }

          //Criando uma chave única.
          $key = 'produtos_busca_' . md5($search);

          return Cache::remember($key, now()->addSeconds(15), function () use ($search) {
              return Produto::select('id', 'nome', 'descricao', 'estoque')
                  ->with(['tamanhos', 'imagens'
                  ])
                  ->where(function ($query) use ($search) {
                      $query->where('nome', 'like', "%{$search}%")
                            ->orWhere('descricao', 'like', "%{$search}%");
                  })
                  ->orderByDesc('id')
                  ->limit(10)
                  ->get();
          });
      }

      public function buscarPaciente(Request $request)
      {
          $search = trim($request->query('search', ''));
          $empresaId = Auth::user()->empresa_id;

          if ($search === '' || !$empresaId) {
              return response()->json([]);
          }

          $key = 'pacientes_busca_' . $empresaId . '_' . md5($search);

          return Cache::remember($key, now()->addSeconds(15), function () use ($search, $empresaId) {
              return Paciente::select('id', 'nome', 'telefone', 'cpf', 'email')
                  ->where('empresa_id', $empresaId)
                  ->where(function ($query) use ($search) {
                      $query->where('nome', 'like', "%{$search}%")
                            ->orWhere('cpf', 'like', "%{$search}%")
                            ->orWhere('telefone', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orderBy('nome')
                  ->limit(10)
                  ->get();
          });
      }
}
