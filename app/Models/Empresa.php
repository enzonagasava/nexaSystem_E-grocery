<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\TipoEmpresa;
use App\Models\TipoPainel;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Empresa extends BaseModel
{
        protected $connection = 'tenant_content';

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
        'tipo_painel_id',
    ];


    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'empresa_id');
    }

    public function redesSociais(): HasMany
    {
        return $this->hasMany(RedeSocial::class, 'empresa_id');
    }

    public function tipoPainel(): BelongsTo
    {
        return $this->belongsTo(TipoPainel::class, 'tipo_painel_id');
    }
}
