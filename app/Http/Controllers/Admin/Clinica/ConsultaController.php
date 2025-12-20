<?php

namespace App\Http\Controllers\Admin\Clinica;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clinica\ConsultaStoreRequest;
use App\Models\Agendamento;
use App\Models\Consulta;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ConsultaController extends Controller
{
    /**
     * Retorna o empresa_id do usuário logado.
     */
    private function getEmpresaId(): int
    {
        return Auth::user()->empresa_id;
    }

    /**
     * Lista todas as consultas.
     */
    public function index(Request $request): Response
    {
        $statusFiltro = $request->input('status', 'todas');

        $query = Consulta::where('empresa_id', $this->getEmpresaId())
            ->with('paciente:id,nome,telefone');

        if ($statusFiltro !== 'todas' && in_array($statusFiltro, ['agendada', 'em-andamento', 'realizada', 'cancelada'])) {
            $query->where('status', $statusFiltro);
        }

        $consultas = $query->orderBy('data_consulta', 'desc')
            ->orderBy('hora_inicio', 'desc')
            ->get()
            ->map(function ($consulta) {
                return [
                    'id' => $consulta->id,
                    'paciente_id' => $consulta->paciente_id,
                    'paciente_nome' => $consulta->paciente?->nome ?? 'Sem paciente',
                    'paciente_telefone' => $consulta->paciente?->telefone ?? '-',
                    'data_consulta' => $consulta->data_consulta->format('Y-m-d'),
                    'data_formatada' => $consulta->data_formatada,
                    'hora_inicio' => $consulta->hora_inicio,
                    'hora_fim' => $consulta->hora_fim,
                    'horario_formatado' => $consulta->horario_formatado,
                    'tipo' => $consulta->tipo,
                    'status' => $consulta->status,
                    'valor' => $consulta->valor ? number_format($consulta->valor, 2, ',', '.') : '-',
                    'motivo' => $consulta->motivo,
                    'created_at_formatted' => $consulta->created_at_formatted,
                ];
            });

        return Inertia::render('admin/clinica/consultas/Index', [
            'consultas' => $consultas,
            'statusFiltro' => $statusFiltro,
        ]);
    }

    /**
     * Exibe formulário de criação de consulta.
     */
    public function create(): Response
    {
        $pacientes = Paciente::where('empresa_id', $this->getEmpresaId())
            ->orderBy('nome')
            ->get(['id', 'nome', 'telefone', 'cpf']);

        return Inertia::render('admin/clinica/consultas/Create', [
            'pacientes' => $pacientes,
        ]);
    }

    /**
     * Armazena uma nova consulta.
     */
    public function store(ConsultaStoreRequest $request)
    {
        $data = $request->validated();
        $data['empresa_id'] = $this->getEmpresaId();

        // Verificar se o paciente pertence à empresa
        $paciente = Paciente::where('empresa_id', $this->getEmpresaId())
            ->where('id', $data['paciente_id'])
            ->firstOrFail();

        $consulta = Consulta::create($data);

        // Se a consulta foi criada com status "agendada", criar um agendamento automaticamente
        if ($consulta->status === 'agendada') {
            $this->criarAgendamentoDaConsulta($consulta);
        }

        return redirect()->route('admin.clinica.consultas.index')
            ->with('success', 'Consulta cadastrada com sucesso!');
    }

    /**
     * Cria um agendamento vinculado à consulta.
     */
    private function criarAgendamentoDaConsulta(Consulta $consulta): Agendamento
    {
        // Calcular duração em minutos baseado em hora_inicio e hora_fim
        $duracao = 30; // Padrão de 30 minutos
        if ($consulta->hora_inicio && $consulta->hora_fim) {
            $inicio = \Carbon\Carbon::parse($consulta->hora_inicio);
            $fim = \Carbon\Carbon::parse($consulta->hora_fim);
            $duracao = $inicio->diffInMinutes($fim);
        }

        return Agendamento::create([
            'empresa_id' => $consulta->empresa_id,
            'paciente_id' => $consulta->paciente_id,
            'consulta_id' => $consulta->id,
            'data' => $consulta->data_consulta,
            'hora' => $consulta->hora_inicio,
            'duracao_minutos' => $duracao > 0 ? $duracao : 30,
            'tipo' => $consulta->tipo ?? 'Consulta',
            'status' => 'confirmado',
            'observacoes' => $consulta->motivo,
        ]);
    }

    /**
     * Atualiza o agendamento vinculado à consulta.
     */
    private function atualizarAgendamentoDaConsulta(Consulta $consulta): void
    {
        $agendamento = $consulta->agendamento;

        if (!$agendamento) {
            return;
        }

        // Calcular duração
        $duracao = 30;
        if ($consulta->hora_inicio && $consulta->hora_fim) {
            $inicio = \Carbon\Carbon::parse($consulta->hora_inicio);
            $fim = \Carbon\Carbon::parse($consulta->hora_fim);
            $duracao = $inicio->diffInMinutes($fim);
        }

        // Mapear status da consulta para status do agendamento
        $statusAgendamento = match ($consulta->status) {
            'agendada' => 'confirmado',
            'em-andamento' => 'confirmado',
            'realizada' => 'realizado',
            'cancelada' => 'cancelado',
            default => 'pendente',
        };

        $agendamento->update([
            'paciente_id' => $consulta->paciente_id,
            'data' => $consulta->data_consulta,
            'hora' => $consulta->hora_inicio,
            'duracao_minutos' => $duracao > 0 ? $duracao : 30,
            'tipo' => $consulta->tipo ?? 'Consulta',
            'status' => $statusAgendamento,
            'observacoes' => $consulta->motivo,
        ]);
    }

    /**
     * Exibe uma consulta específica.
     */
    public function show(string $id): Response
    {
        $consulta = Consulta::where('empresa_id', $this->getEmpresaId())
            ->with('paciente')
            ->findOrFail($id);

        return Inertia::render('admin/clinica/consultas/Show', [
            'consulta' => $consulta,
        ]);
    }

    /**
     * Exibe formulário de edição de consulta.
     */
    public function edit(string $id): Response
    {
        $consulta = Consulta::where('empresa_id', $this->getEmpresaId())
            ->with('paciente:id,nome')
            ->findOrFail($id);

        $pacientes = Paciente::where('empresa_id', $this->getEmpresaId())
            ->orderBy('nome')
            ->get(['id', 'nome', 'telefone', 'cpf']);

        return Inertia::render('admin/clinica/consultas/Edit', [
            'consulta' => $consulta,
            'pacientes' => $pacientes,
        ]);
    }

    /**
     * Atualiza uma consulta.
     */
    public function update(ConsultaStoreRequest $request, string $id)
    {
        $consulta = Consulta::where('empresa_id', $this->getEmpresaId())
            ->with('agendamento')
            ->findOrFail($id);

        $statusAnterior = $consulta->status;

        // Verificar se o paciente pertence à empresa
        $paciente = Paciente::where('empresa_id', $this->getEmpresaId())
            ->where('id', $request->paciente_id)
            ->firstOrFail();

        $consulta->update($request->validated());

        // Sincronizar agendamento
        if ($consulta->status === 'agendada' && !$consulta->agendamento) {
            // Se agora está agendada e não tinha agendamento, criar
            $this->criarAgendamentoDaConsulta($consulta);
        } elseif ($consulta->agendamento) {
            // Se tem agendamento, atualizar
            $this->atualizarAgendamentoDaConsulta($consulta);
        }

        return redirect()->route('admin.clinica.consultas.index')
            ->with('success', 'Consulta atualizada com sucesso!');
    }

    /**
     * Remove uma consulta.
     */
    public function destroy(string $id)
    {
        $consulta = Consulta::where('empresa_id', $this->getEmpresaId())
            ->findOrFail($id);

        $consulta->delete();

        return redirect()->route('admin.clinica.consultas.index')
            ->with('success', 'Consulta removida com sucesso!');
    }

    /**
     * Atualiza o status da consulta.
     */
    public function atualizarStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:agendada,em-andamento,realizada,cancelada',
        ]);

        $consulta = Consulta::where('empresa_id', $this->getEmpresaId())
            ->with('agendamento')
            ->findOrFail($id);

        $consulta->update(['status' => $request->status]);

        // Se está sendo agendada e não tinha agendamento, criar
        if ($request->status === 'agendada' && !$consulta->agendamento) {
            $this->criarAgendamentoDaConsulta($consulta);
        } elseif ($consulta->agendamento) {
            // Se tem agendamento, atualizar status
            $this->atualizarAgendamentoDaConsulta($consulta);
        }

        return redirect()->back()
            ->with('success', 'Status atualizado com sucesso!');
    }
}
