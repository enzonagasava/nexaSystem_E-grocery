<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\BaseModel;

class Plataforma extends BaseModel
{
    protected $connection = 'tenant_content';

    protected $table = 'plataforma_pedido';

    protected $fillable = [
        'nome',
    ];
}
