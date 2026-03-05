<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contato extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant_content';

    protected $fillable = [
        'lead_id',
        'banco_nome',
        'banco_codigo',
        'agencia',
        'conta',
        'conta_tipo',
        'pix',
        'pix_tipo',
        'profissao',
        'empresa',
        'renda_mensal',
        'status',
        'tipo_relacao',
        'nivel_interesse',
        'ultimo_contato',
        'observacoes',
        'corretor_id'
    ];

    protected $casts = [
        'preferencias_imoveis' => 'array',
        'renda_mensal' => 'decimal:2',
        'ultimo_contato' => 'date'
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function corretor()
    {
        return $this->belongsTo(User::class, 'corretor_id');
    }
}