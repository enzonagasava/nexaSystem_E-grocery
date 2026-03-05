<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\BaseModel;

class Plataforma extends BaseModel
{
<<<<<<< HEAD
    protected $connection = 'tenant_content';
=======
    protected $connection = 'content';
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f

    protected $table = 'plataforma_pedido';

    protected $fillable = [
        'nome',
    ];
}
