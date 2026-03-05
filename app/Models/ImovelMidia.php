<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImovelMidia extends Model
{
    protected $connection = 'tenant_content';
    protected $table = 'imovel_midias';

    protected $fillable = [
        'imovel_id','user_id','tipo','path','ordem'
    ];

    public function imovel()
    {
        return $this->belongsTo(Imovel::class, 'imovel_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
