<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoricoMensagem extends Model
{
<<<<<<< HEAD
    
=======
    protected $connection = 'content';
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f

    protected $table = 'historico_mensagens';

    protected $fillable = [
        'remote_jid',
        'message_id',
        'conteudo',
        'de_mim',
        'processado_ia',
        'enviado_em'
    ];

    protected $casts = [
        'de_mim' => 'boolean',
        'processado_ia' => 'boolean',
        'enviado_em' => 'datetime',
    ];
}
