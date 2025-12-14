<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WhatsAppChat extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_conversas';

    protected $fillable = [
        'contato_id',
        'status',
        'last_message',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function contact()
    {
        return $this->belongsTo(WhatsAppContatos::class, 'contato_id');
    }

    public function messages()
    {
        return $this->hasMany(WhatsAppMessage::class, 'chat_id');
    }
}
