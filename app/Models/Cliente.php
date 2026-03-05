<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;

class Cliente extends BaseModel
{
<<<<<<< HEAD
    protected $connection = 'tenant_content';

=======
    protected $connection = 'content';
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f

    protected $table = 'clientes';


    protected $fillable = [
        'nome',
        'email',
        'numero',
        'endereco',
        'cep',
        'numero_endereco',
        'municipio',
        'estado',
    ];


    protected $appends = ['created_at_formatted'];

}
