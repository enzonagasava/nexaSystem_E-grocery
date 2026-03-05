<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Permissao extends Model
{
    protected $connection = 'tenant_credentials';

    protected $table = 'permissoes';

    protected $fillable = [
        'nome',
        'recurso',
        'modulo_id',
        'display_name',
    ];

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulo::class);
    }

    /**
     * Nome completo para checagem (ex.: chat.visualizar).
     */
    public function getNomeCompletoAttribute(): string
    {
        return "{$this->recurso}.{$this->nome}";
    }
}
