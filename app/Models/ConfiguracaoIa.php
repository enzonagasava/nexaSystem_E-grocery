<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracaoIa extends Model
{
    protected $table = 'configuracoes_ia';

    protected $fillable = [
        'bot_ativo',
        'tom_voz',
        'mensagem_boas_vindas',
        'mensagem_fora_horario',
        'timer_ausencia',
        'bloquear_bot',
        'bloqueio_ate'
    ];

    protected $casts = [
        'bot_ativo' => 'boolean',
        'bloquear_bot' => 'boolean',
        'bloqueio_ate' => 'datetime',
    ];

    public static function getConfig()
    {
        return self::firstOrCreate([]);
    }
}
