<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    protected $connection = 'credentials';

    protected $table = 'cargos';

    protected $fillable = [
        'nome',
        'descricao',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'cargo_id');
    }
}
