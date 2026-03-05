<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WhatsAppContatos extends Model
{
    use HasFactory;

<<<<<<< HEAD
    protected $connection = 'tenant_content';
=======
    protected $connection = 'content';
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f

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
