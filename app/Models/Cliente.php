<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;

class Cliente extends BaseModel
{
    protected $connection = 'tenant_content';


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
