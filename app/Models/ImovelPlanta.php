<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImovelPlanta extends Model
{
    protected $connection = 'tenant_content';
    protected $table = 'imovel_plantas';

    protected $fillable = ['imovel_id','user_id','path','original_name','mime_type','size','uploaded_at'];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function imovel()
    {
        return $this->belongsTo(Imovel::class, 'imovel_id');
    }
}
