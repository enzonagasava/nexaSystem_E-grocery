<?php

namespace App\Console\Commands;

use App\Models\Agendamento;
use App\Models\Consulta;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SincronizarAgendamentosConsultas extends Command
{
    protected $signature = 'clinica:sincronizar-agendamentos';

    protected $description = 'Cria agendamentos para consultas agendadas que não possuem agendamento vinculado';

    public function handle(): int
    {
        $consultas = Consulta::where('status', 'agendada')
            ->whereDoesntHave('agendamento')
            ->get();

        if ($consultas->isEmpty()) {
            $this->info('Nenhuma consulta pendente de sincronização.');
            return Command::SUCCESS;
        }

        $this->info("Encontradas {$consultas->count()} consultas para sincronizar...");

        foreach ($consultas as $consulta) {
            $duracao = 30;
            if ($consulta->hora_inicio && $consulta->hora_fim) {
                $inicio = Carbon::parse($consulta->hora_inicio);
                $fim = Carbon::parse($consulta->hora_fim);
                $duracao = $inicio->diffInMinutes($fim);
            }

            Agendamento::create([
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

            $this->line("✓ Agendamento criado para consulta #{$consulta->id}");
        }

        $this->info('Sincronização concluída!');

        return Command::SUCCESS;
    }
}
