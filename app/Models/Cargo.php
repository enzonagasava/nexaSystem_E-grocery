<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cargo extends Model
{
<<<<<<< HEAD
    protected $connection = 'tenant_credentials';
=======
    protected $connection = 'credentials';
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f

    protected $table = 'cargos';

    protected $fillable = [
        'nome',
        'descricao',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'cargo_id');
    }

    public function permissoes(): BelongsToMany
    {
        return $this->belongsToMany(Permissao::class, 'cargo_permissao')
            ->withPivot('painel_id')
            ->withTimestamps();
    }
}
