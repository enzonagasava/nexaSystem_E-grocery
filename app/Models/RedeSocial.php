<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\BaseModel;
use App\Models\Empresa;

class RedeSocial extends BaseModel
{
    protected $connection = 'tenant_content';
    

    protected $table = 'redes_sociais';

    protected $fillable = [
        'facebook',
        'instagram',
        'linkedin',
        'youtube',
        'tiktok',
        'x',
        'empresa_id',
    ];

     public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
