<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WhatsAppContatos extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_contatos';

    protected $fillable = [
        'nome',
        'numero',
        'foto_perfil',
        'last_message',
        'last_message_at',
    ];

    public function chats()
    {
        return $this->hasMany(WhatsAppChat::class, 'contato_id')
            ->orderBy('created_at', 'asc');
    }
}
