<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\TipoEmpresa;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'numero',
        'password',
        'cargo_id',
        'empresa_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function anuncios()
    {
        return $this->hasMany(Produto::class);
    }

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Cargo::class, 'cargo_id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    /**
     * Retorna o tipo da empresa do usuário.
     */
    public function getTipoEmpresa(): ?TipoEmpresa
    {
        return $this->empresa?->tipo;
    }

    /**
     * Verifica se o usuário pertence a uma empresa do tipo especificado.
     */
    public function isEmpresaTipo(TipoEmpresa|string $tipo): bool
    {
        if (is_string($tipo)) {
            $tipo = TipoEmpresa::tryFrom($tipo);
        }

        return $this->getTipoEmpresa() === $tipo;
    }

    /**
     * Retorna a rota do dashboard apropriada para o usuário.
     */
    public function getDashboardRoute(): string
    {
        // Admin master sem empresa vai para dashboard admin geral
        if ($this->cargo_id === 1 && !$this->empresa_id) {
            return 'admin.dashboard';
        }

        // Usuário com empresa vai para dashboard do tipo
        $tipoEmpresa = $this->getTipoEmpresa();
        if ($tipoEmpresa) {
            return $tipoEmpresa->dashboardRoute();
        }

        // Fallback para cliente sem empresa
        return 'cliente.dashboard';
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Retorna um array com as claims customizadas para o JWT
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

}
