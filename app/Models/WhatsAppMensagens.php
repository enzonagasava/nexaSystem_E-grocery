<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WhatsAppMessage extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_mensagens';

    protected $fillable = [
        'conversas_id',
        'direction',
        'type',
        'body',
        'wamid',
        'sent_by_human'
    ];


    protected $casts = [
        'sent_by_human' => 'boolean',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];;

    public function chat()
    {
        return $this->belongsTo(WhatsAppChat::class, 'conversas_id');
    }
}
