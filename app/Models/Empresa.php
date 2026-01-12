<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\TipoEmpresa;

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
        'tipo',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoEmpresa::class,
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'empresa_id');
    }

    public function redesSociais(): HasMany
    {
        return $this->hasMany(RedeSocial::class, 'empresa_id');
    }

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
     * Verifica se a empresa é do tipo corretor de imóveis.
     */
    public function isCorretor(): bool
    {
        return $this->tipo === TipoEmpresa::Corretor;
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
    }
}
