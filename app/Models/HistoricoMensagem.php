<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoricoMensagem extends Model
{
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
