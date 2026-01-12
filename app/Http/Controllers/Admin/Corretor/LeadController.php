<?php

namespace App\Http\Controllers\Admin\Corretor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
    /**
     * Exibe a listagem de leads.
     */
    public function index(): Response
    {
        return Inertia::render('admin/corretor/leads/Index', [
            'leads' => [],
        ]);
    }

    /**
     * Exibe o formulário de criação de lead.
     */
    public function create(): Response
    {
        return Inertia::render('admin/corretor/leads/Create');
    }

    /**
     * Armazena um novo lead.
     */
    public function store(Request $request)
    {
        // TODO: Implementar lógica de criação
        return redirect()->route('admin.corretor.leads.index')
            ->with('success', 'Lead cadastrado com sucesso!');
    }

    /**
     * Exibe um lead específico.
     */
    public function show(string $id): Response
    {
        return Inertia::render('admin/corretor/leads/Show', [
            'lead' => [],
        ]);
    }

    /**
     * Exibe o formulário de edição de lead.
     */
    public function edit(string $id): Response
    {
        return Inertia::render('admin/corretor/leads/Edit', [
            'lead' => [],
        ]);
    }

    /**
     * Atualiza um lead existente.
     */
    public function update(Request $request, string $id)
    {
        // TODO: Implementar lógica de atualização
        return redirect()->route('admin.corretor.leads.index')
            ->with('success', 'Lead atualizado com sucesso!');
    }

    /**
     * Remove um lead.
     */
    public function destroy(string $id)
    {
        // TODO: Implementar lógica de exclusão
        return redirect()->route('admin.corretor.leads.index')
            ->with('success', 'Lead removido com sucesso!');
    }
}
