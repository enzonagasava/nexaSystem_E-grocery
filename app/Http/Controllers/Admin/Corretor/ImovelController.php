<?php

namespace App\Http\Controllers\Admin\Corretor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ImovelController extends Controller
{
    /**
     * Exibe a listagem de imóveis.
     */
    public function index(): Response
    {
        return Inertia::render('admin/corretor/imoveis/Index', [
            'imoveis' => [],
        ]);
    }

    /**
     * Exibe o formulário de criação de imóvel.
     */
    public function create(): Response
    {
        return Inertia::render('admin/corretor/imoveis/Create');
    }

    /**
     * Armazena um novo imóvel.
     */
    public function store(Request $request)
    {
        // TODO: Implementar lógica de criação
        return redirect()->route('admin.corretor.imoveis.index')
            ->with('success', 'Imóvel cadastrado com sucesso!');
    }

    /**
     * Exibe um imóvel específico.
     */
    public function show(string $id): Response
    {
        return Inertia::render('admin/corretor/imoveis/Show', [
            'imovel' => [],
        ]);
    }

    /**
     * Exibe o formulário de edição de imóvel.
     */
    public function edit(string $id): Response
    {
        return Inertia::render('admin/corretor/imoveis/Edit', [
            'imovel' => [],
        ]);
    }

    /**
     * Atualiza um imóvel existente.
     */
    public function update(Request $request, string $id)
    {
        // TODO: Implementar lógica de atualização
        return redirect()->route('admin.corretor.imoveis.index')
            ->with('success', 'Imóvel atualizado com sucesso!');
    }

    /**
     * Remove um imóvel.
     */
    public function destroy(string $id)
    {
        // TODO: Implementar lógica de exclusão
        return redirect()->route('admin.corretor.imoveis.index')
            ->with('success', 'Imóvel removido com sucesso!');
    }
}
