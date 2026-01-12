<?php

namespace App\Http\Controllers\Admin\Clinica;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clinica\PacienteStoreRequest;
use App\Models\Paciente;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PacienteController extends Controller
{
    /**
     * Retorna o empresa_id do usuário logado.
     */
    private function getEmpresaId(): int
    {
        return Auth::user()->empresa_id;
    }

    /**
     * Lista todos os pacientes.
     */
    public function index(): Response
    {
        $pacientes = Paciente::where('empresa_id', $this->getEmpresaId())
            ->orderBy('nome')
            ->get();

        return Inertia::render('admin/clinica/pacientes/Index', [
            'pacientes' => $pacientes,
        ]);
    }

    /**
     * Exibe formulário de criação de paciente.
     */
    public function create(): Response
    {
        return Inertia::render('admin/clinica/pacientes/Create');
    }

    /**
     * Armazena um novo paciente.
     */
    public function store(PacienteStoreRequest $request)
    {
        $data = $request->validated();
        $data['empresa_id'] = $this->getEmpresaId();

        Paciente::create($data);

        return redirect()->route('admin.clinica.pacientes.index')
            ->with('success', 'Paciente cadastrado com sucesso!');
    }

    /**
     * Exibe um paciente específico.
     */
    public function show(string $id): Response
    {
        $paciente = Paciente::where('empresa_id', $this->getEmpresaId())
            ->with(['consultas' => function ($query) {
                $query->orderBy('data_consulta', 'desc')->limit(10);
            }, 'agendamentos' => function ($query) {
                $query->orderBy('data', 'desc')->limit(10);
            }])
            ->findOrFail($id);

        return Inertia::render('admin/clinica/pacientes/Show', [
            'paciente' => $paciente,
        ]);
    }

    /**
     * Exibe formulário de edição de paciente.
     */
    public function edit(string $id): Response
    {
        $paciente = Paciente::where('empresa_id', $this->getEmpresaId())
            ->findOrFail($id);

        return Inertia::render('admin/clinica/pacientes/Edit', [
            'paciente' => $paciente,
        ]);
    }

    /**
     * Atualiza um paciente.
     */
    public function update(PacienteStoreRequest $request, string $id)
    {
        $paciente = Paciente::where('empresa_id', $this->getEmpresaId())
            ->findOrFail($id);

        $paciente->update($request->validated());

        return redirect()->route('admin.clinica.pacientes.index')
            ->with('success', 'Paciente atualizado com sucesso!');
    }

    /**
     * Remove um paciente.
     */
    public function destroy(string $id)
    {
        $paciente = Paciente::where('empresa_id', $this->getEmpresaId())
            ->findOrFail($id);

        $paciente->delete();

        return redirect()->route('admin.clinica.pacientes.index')
            ->with('success', 'Paciente removido com sucesso!');
    }
}
