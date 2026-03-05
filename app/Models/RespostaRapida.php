<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RespostaRapida extends Model
{
<<<<<<< HEAD
    protected $connection = 'tenant_content';
=======
    protected $connection = 'content';
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f

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
