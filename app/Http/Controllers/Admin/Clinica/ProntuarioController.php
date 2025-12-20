<?php

namespace App\Http\Controllers\Admin\Clinica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProntuarioController extends Controller
{
    /**
     * Lista todos os prontuários.
     */
    public function index(): Response
    {
        return Inertia::render('admin/clinica/prontuarios/Index', [
            'prontuarios' => [],
        ]);
    }

    /**
     * Exibe formulário de criação de prontuário.
     */
    public function create(): Response
    {
        return Inertia::render('admin/clinica/prontuarios/Create');
    }

    /**
     * Armazena um novo prontuário.
     */
    public function store(Request $request)
    {
        // Validação e criação do prontuário virão aqui
        return redirect()->route('admin.clinica.prontuarios.index')
            ->with('success', 'Prontuário cadastrado com sucesso!');
    }

    /**
     * Exibe um prontuário específico.
     */
    public function show(string $id): Response
    {
        return Inertia::render('admin/clinica/prontuarios/Show', [
            'prontuario' => [],
        ]);
    }

    /**
     * Exibe formulário de edição de prontuário.
     */
    public function edit(string $id): Response
    {
        return Inertia::render('admin/clinica/prontuarios/Edit', [
            'prontuario' => [],
        ]);
    }

    /**
     * Atualiza um prontuário.
     */
    public function update(Request $request, string $id)
    {
        // Validação e atualização do prontuário virão aqui
        return redirect()->route('admin.clinica.prontuarios.index')
            ->with('success', 'Prontuário atualizado com sucesso!');
    }

    /**
     * Remove um prontuário.
     */
    public function destroy(string $id)
    {
        // Exclusão do prontuário virá aqui
        return redirect()->route('admin.clinica.prontuarios.index')
            ->with('success', 'Prontuário removido com sucesso!');
    }
}
