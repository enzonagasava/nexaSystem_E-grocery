<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Cliente;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;


class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::all()->map(function ($cliente) {
            $cliente->endereco_completo = "{$cliente->endereco}, {$cliente->numero_endereco} - {$cliente->municipio} - {$cliente->estado}";
            return $cliente;
        });

        return Inertia::render('admin/ecommerce/clientes/Clientes', [
            'clientes' => $clientes
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/ecommerce/clientes/AdicionarClientes');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
<<<<<<< HEAD
            'email' => 'nullable|string|email|max:255|unique:tenant_content.clientes',
            'numero' => 'required|string|max:20|unique:tenant_content.clientes',
=======
            'email' => 'nullable|string|email|max:255|unique:content.clientes',
            'numero' => 'required|string|max:20|unique:content.clientes',
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
            'cep' => 'nullable|string|max:10',
            'endereco' => 'nullable|string|max:255',
            'numero_endereco' => 'nullable|string|max:10',
            'municipio' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:100',
        ]);

        Cliente::create($validated);

        return Inertia::location(route('admin.ecommerce.clientes.index'));
    }

    public function edit($id){
        $cliente = Cliente::findOrFail($id);

        return Inertia::render('admin/ecommerce/clientes/EditarClientes', [
            'cliente' => $cliente
        ]);
    }

    public function update(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
<<<<<<< HEAD
            'email' => 'nullable|string|email|max:255|unique:tenant_content.clientes,email,' . $id,
            'numero' => 'required|string|max:20|unique:tenant_content.clientes,numero,' . $id,
=======
            'email' => 'nullable|string|email|max:255|unique:content.clientes,email,' . $id,
            'numero' => 'required|string|max:20|unique:content.clientes,numero,' . $id,
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
            'cep' => 'nullable|string|max:10',
            'endereco' => 'nullable|string|max:255',
            'numero_endereco' => 'nullable|string|max:10',
            'municipio' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:100',
        ]);

        $cliente->update($validated);

        return Inertia::location(route('admin.ecommerce.clientes.index'));
    }

    public function destroy($id){
        $cliente = Cliente::findOrFail($id);
        $cliente->delete();

        return Inertia::location(route('admin.ecommerce.clientes.index'));
    }

    public function buscar(Request $request)
    {
        $search = trim($request->query('search', ''));

        if ($search === '') {
            return response()->json([]);
        }

        $key = 'clientes_busca_' . md5($search);

        return Cache::remember($key, now()->addSeconds(5), function () use ($search) {
            // FULLTEXT super rápido
            $clientes = Cliente::select('id', 'nome', 'email')
                ->whereRaw("MATCH(nome, email) AGAINST(? IN BOOLEAN MODE)", [$search . '*'])
                ->limit(8)
                ->get();

            // fallback pra LIKE se fulltext não encontrar nada
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


}

