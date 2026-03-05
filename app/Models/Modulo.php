<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Modulo extends BaseModel
{
    protected $connection = 'tenant_credentials';

    protected $fillable = ['nome', 'display_name', 'descricao'];

    /**
     * TipoPainel (nexa_admin) ligados a este módulo via painel_modulo (tenant_credentials).
     * Pivot em tenant_credentials; TipoPainel carregado de nexa_admin.
     */
    public function tipoPaineis(): BelongsToMany
    {
        return $this->belongsToMany(TipoPainel::class, 'painel_modulo', 'modulo_id', 'painel_id');
    }

    public function permissoes(): HasMany
    {
        return $this->hasMany(Permissao::class);
    }
}