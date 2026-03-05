<?php

namespace App\Models;

use App\Models\BaseModel;

class Agendamento extends BaseModel
{
<<<<<<< HEAD
    
    protected $connection = 'tenant_content';
=======
    protected $connection = 'content';
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f

    protected $table = 'agendamentos';

    protected $fillable = [
        'empresa_id',
        'paciente_id',
        'consulta_id',
        'data',
        'hora',
        'duracao_minutos',
        'tipo',
        'status',
        'observacoes',
    ];

    protected $appends = ['created_at_formatted', 'data_formatada', 'hora_formatada'];

    protected $casts = [
        'data' => 'date',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function consulta()
    {
        return $this->belongsTo(Consulta::class);
    }

    public function getDataFormatadaAttribute(): string
    {
        return $this->data?->format('d/m/Y') ?? '-';
    }

    public function getHoraFormatadaAttribute(): string
    {
        return $this->hora ? substr($this->hora, 0, 5) : '-';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pendente' => 'Pendente',
            'confirmado' => 'Confirmado',
            'cancelado' => 'Cancelado',
            'realizado' => 'Realizado',
            default => $this->status,
        };
    }
}
