<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImovelAutorizacao extends Model
{
    protected $connection = 'tenant_content';
    protected $table = 'imovel_autorizacoes';

    protected $fillable = [
        'imovel_id',
        'user_id',
        'path',
        'original_name',
        'mime_type',
        'size',
        'checksum',
        'uploaded_at',
        'proprietario_nome',
        'proprietario_telefone',
        'proprietario_email',
        'proprietario_documento',
    ];

    public function imovel()
    {
        return $this->belongsTo(Imovel::class, 'imovel_id');
    }
}
