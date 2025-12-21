<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\BaseModel;
use App\Models\RedeSocial;

class Empresa extends BaseModel
{
        protected $connection = 'content';

        protected $fillable = [
        'nome',
        'email',
        'numero_wpp',
        'telefone',
        'cnpj',
        'endereco',
        'cep',
        'numero_endereco',
        'municipio',
        'estado',
        'logo',
    ];

    public function redesSociais()
    {
        return $this->hasMany(RedeSocial::class, 'empresa_id');
    }
}
