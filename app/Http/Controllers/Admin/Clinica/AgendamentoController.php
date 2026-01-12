<?php

namespace App\Http\Controllers\Admin\Clinica;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clinica\AgendamentoStoreRequest;
use App\Models\Agendamento;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AgendamentoController extends Controller
{
    /**
     * Retorna o empresa_id do usuário logado.
     */
    private function getEmpresaId(): int
    {
        return Auth::user()->empresa_id;
    }

    /**
     * Lista todos os agendamentos.
     */
    public function index(Request $request): Response
    {
        $statusFiltro = $request->input('status', 'todos');

        $query = Agendamento::where('empresa_id', $this->getEmpresaId())
            ->with('paciente:id,nome,telefone');

        if ($statusFiltro !== 'todos' && in_array($statusFiltro, ['pendente', 'confirmado', 'cancelado', 'realizado'])) {
            $query->where('status', $statusFiltro);
        }

        $agendamentos = $query->orderBy('data', 'desc')
            ->orderBy('hora', 'desc')
            ->get()
            ->map(function ($agendamento) {
                return [
                    'id' => $agendamento->id,
                    'paciente_id' => $agendamento->paciente_id,
                    'paciente_nome' => $agendamento->paciente?->nome ?? 'Sem paciente',
                    'paciente_telefone' => $agendamento->paciente?->telefone ?? '-',
                    'data' => $agendamento->data->format('Y-m-d'),
                    'data_formatada' => $agendamento->data_formatada,
                    'hora' => $agendamento->hora,
                    'hora_formatada' => $agendamento->hora_formatada,
                    'duracao_minutos' => $agendamento->duracao_minutos,
                    'tipo' => $agendamento->tipo,
                    'status' => $agendamento->status,
                    'observacoes' => $agendamento->observacoes,
                    'created_at_formatted' => $agendamento->created_at_formatted,
                ];
            });

        return Inertia::render('admin/clinica/agendamentos/Index', [
            'agendamentos' => $agendamentos,
            'statusFiltro' => $statusFiltro,
        ]);
    }

    /**
     * Exibe formulário de criação de agendamento.
     */
    public function create(): Response
    {
        $pacientes = Paciente::where('empresa_id', $this->getEmpresaId())
            ->orderBy('nome')
            ->get(['id', 'nome', 'telefone', 'cpf']);

        return Inertia::render('admin/clinica/agendamentos/Create', [
            'pacientes' => $pacientes,
        ]);
    }

    /**
     * Armazena um novo agendamento.
     */
    public function store(AgendamentoStoreRequest $request)
    {
        $data = $request->validated();
        $data['empresa_id'] = $this->getEmpresaId();

        // Verificar se o paciente pertence à empresa
        $paciente = Paciente::where('empresa_id', $this->getEmpresaId())
            ->where('id', $data['paciente_id'])
            ->firstOrFail();

        Agendamento::create($data);

        return redirect()->route('admin.clinica.agendamentos.index')
            ->with('success', 'Agendamento cadastrado com sucesso!');
    }

    /**
     * Exibe um agendamento específico.
     */
    public function show(string $id): Response
    {
        $agendamento = Agendamento::where('empresa_id', $this->getEmpresaId())
            ->with('paciente')
            ->findOrFail($id);

        return Inertia::render('admin/clinica/agendamentos/Show', [
            'agendamento' => $agendamento,
        ]);
    }

    /**
     * Exibe formulário de edição de agendamento.
     */
    public function edit(string $id): Response
    {
        $agendamento = Agendamento::where('empresa_id', $this->getEmpresaId())
            ->with('paciente:id,nome')
            ->findOrFail($id);

        $pacientes = Paciente::where('empresa_id', $this->getEmpresaId())
            ->orderBy('nome')
            ->get(['id', 'nome', 'telefone', 'cpf']);

        return Inertia::render('admin/clinica/agendamentos/Edit', [
            'agendamento' => $agendamento,
            'pacientes' => $pacientes,
        ]);
    }

    /**
     * Atualiza um agendamento.
     */
    public function update(AgendamentoStoreRequest $request, string $id)
    {
        $agendamento = Agendamento::where('empresa_id', $this->getEmpresaId())
            ->findOrFail($id);

        // Verificar se o paciente pertence à empresa
        $paciente = Paciente::where('empresa_id', $this->getEmpresaId())
            ->where('id', $request->paciente_id)
            ->firstOrFail();

        $agendamento->update($request->validated());

        return redirect()->route('admin.clinica.agendamentos.index')
            ->with('success', 'Agendamento atualizado com sucesso!');
    }

    /**
     * Remove um agendamento.
     */
    public function destroy(string $id)
    {
        $agendamento = Agendamento::where('empresa_id', $this->getEmpresaId())
            ->findOrFail($id);

        $agendamento->delete();

        return redirect()->route('admin.clinica.agendamentos.index')
            ->with('success', 'Agendamento removido com sucesso!');
    }

    /**
     * Atualiza o status do agendamento.
     */
    public function atualizarStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:pendente,confirmado,cancelado,realizado',
        ]);

        $agendamento = Agendamento::where('empresa_id', $this->getEmpresaId())
            ->findOrFail($id);

        $agendamento->update(['status' => $request->status]);

        return redirect()->back()
            ->with('success', 'Status atualizado com sucesso!');
    }
}
