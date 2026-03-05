<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\TipoEmpresa;
<<<<<<< HEAD
use App\Models\TipoPainel;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Empresa extends BaseModel
{
        protected $connection = 'tenant_content';
=======

class Empresa extends BaseModel
{
        protected $connection = 'content';
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f

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
<<<<<<< HEAD
        'tipo_painel_id',
    ];

=======
        'tipo',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoEmpresa::class,
        ];
    }
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'empresa_id');
    }

    public function redesSociais(): HasMany
    {
        return $this->hasMany(RedeSocial::class, 'empresa_id');
    }

<<<<<<< HEAD
    public function tipoPainel(): BelongsTo
    {
        return $this->belongsTo(TipoPainel::class, 'tipo_painel_id');
=======
    /**
     * Verifica se a empresa é do tipo e-commerce.
     */
    public function isEcommerce(): bool
    {
        return $this->tipo === TipoEmpresa::Ecommerce;
    }

    /**
     * Verifica se a empresa é do tipo clínica.
     */
    public function isClinica(): bool
    {
        return $this->tipo === TipoEmpresa::Clinica;
    }

    /**
     * Verifica se a empresa é do tipo especificado.
     */
    public function isTipo(TipoEmpresa|string $tipo): bool
    {
        if (is_string($tipo)) {
            $tipo = TipoEmpresa::tryFrom($tipo);
        }

        return $this->tipo === $tipo;
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
    }
}
