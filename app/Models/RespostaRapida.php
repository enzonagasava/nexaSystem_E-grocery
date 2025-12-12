<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RespostaRapida extends Model
{
    protected $table = 'respostas_rapidas';

    protected $fillable = [
        'atalho',
        'mensagem',
        'ativo'
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];
}
