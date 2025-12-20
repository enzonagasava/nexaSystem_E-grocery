<?php

namespace App\Models;

use App\Models\BaseModel;

class Consulta extends BaseModel
{
    protected $table = 'consultas';

    protected $fillable = [
        'empresa_id',
        'paciente_id',
        'data_consulta',
        'hora_inicio',
        'hora_fim',
        'tipo',
        'status',
        'valor',
        'motivo',
        'observacoes',
        'diagnostico',
        'prescricao',
    ];

    protected $appends = ['created_at_formatted', 'data_formatada', 'horario_formatado'];

    protected $casts = [
        'data_consulta' => 'date',
        'valor' => 'decimal:2',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function agendamento()
    {
        return $this->hasOne(Agendamento::class);
    }

    public function getDataFormatadaAttribute(): string
    {
        return $this->data_consulta?->format('d/m/Y') ?? '-';
    }

    public function getHorarioFormatadoAttribute(): string
    {
        $inicio = $this->hora_inicio ? substr($this->hora_inicio, 0, 5) : '';
        $fim = $this->hora_fim ? substr($this->hora_fim, 0, 5) : '';

        if ($inicio && $fim) {
            return "{$inicio} - {$fim}";
        }

        return $inicio ?: '-';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'agendada' => 'Agendada',
            'em-andamento' => 'Em Andamento',
            'realizada' => 'Realizada',
            'cancelada' => 'Cancelada',
            default => $this->status,
        };
    }
}
