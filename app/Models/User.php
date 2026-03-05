<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
<<<<<<< HEAD
use App\Models\TipoPainel;
use App\Models\Produto;
use App\Enums\TipoEmpresa;
use App\Models\Cargo;
use App\Models\Empresa;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
=======
use App\Enums\TipoEmpresa;
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
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

<<<<<<< HEAD
    protected $connection = 'tenant_credentials';

    protected $table = 'users';
=======
    protected $connection = 'credentials';

>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f

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

<<<<<<< HEAD
        public function paineis()
    {
        return $this->belongsToMany(TipoPainel::class, 'painel_user')
                    ->withPivot('configuracoes')
                    ->withTimestamps();
    }

    public function painelAtivo()
    {
        return session('painel_ativo_id') 
            ? $this->paineis()->where('painel_id', session('painel_ativo_id'))->first()
            : $this->paineis()->first();
    }

    public function temAcessoAoPainel($painelId)
    {
        if ($this->empresa_id && $this->empresa && $this->empresa->tipo_painel_id == $painelId) {
            return true;
        }
        return $this->paineis()->where('painel_id', $painelId)->exists();
    }

    public function temPermissaoNoPainel($permissaoNome, $painelId = null)
    {
        $painelId = $painelId ?? session('painel_ativo_id');

        if (!$painelId || !$this->temAcessoAoPainel($painelId)) {
            return false;
        }

        if (!$this->cargo_id) {
            return false;
        }

        $parts = explode('.', $permissaoNome, 2);
        $recurso = $parts[0] ?? '';
        $nome = $parts[1] ?? $permissaoNome;

        return $this->cargo->permissoes()
            ->where('permissoes.recurso', $recurso)
            ->where('permissoes.nome', $nome)
            ->wherePivot('painel_id', $painelId)
            ->exists();
    }


     // Retorna a lista de permissões do usuário no painel (formato "recurso.acao").
     // Admin master (cargo_id 1 sem empresa) retorna [] — o frontend usa canAll para exibir tudo.

    public function getPermissoesDoPainel(?int $painelId = null): array
    {
        $painelId = $painelId ?? session('painel_ativo_id');

        if (!$painelId || !$this->cargo_id) {
            return [];
        }

        if ($this->cargo_id === 1 && !$this->empresa_id) {
            return [];
        }

        if (!$this->temAcessoAoPainel($painelId)) {
            return [];
        }

        return $this->cargo->permissoes()
            ->wherePivot('painel_id', $painelId)
            ->get()
            ->map(fn ($p) => "{$p->recurso}.{$p->nome}")
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Retorna o tipo da empresa do usuário.
     */
    public function getTipoEmpresa(): ?TipoPainel
    {
        $tipoPainel = TipoPainel::find($this->empresa->tipo_painel_id);
        if (!$tipoPainel) {
            Log::warning('[getTipoEmpresa] Tipo de empresa retornou null', [
                'user_id' => $this->id,
                'email' => $this->email,
                'empresa_id' => $this->empresa_id,
                'empresa_loaded' => $this->relationLoaded('empresa'),
                'empresa_instance' => $this->empresa ? 'Existe instância: ' . $this->empresa->id : 'Nenhuma instância',
                'empresa_tipo_raw' => $this->empresa?->getRawOriginal('tipo'),
            ]);
        }
        return $tipoPainel;
=======
    /**
     * Retorna o tipo da empresa do usuário.
     */
    public function getTipoEmpresa(): ?TipoEmpresa
    {
        return $this->empresa?->tipo;
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
    }

    /**
     * Verifica se o usuário pertence a uma empresa do tipo especificado.
     */
<<<<<<< HEAD
        public function isEmpresaTipo(TipoPainel|string $tipo): bool
        {
            // Obtém o tipo da empresa do usuário
            $tipoUsuario = $this->getTipoEmpresa();

            // Se o usuário não tem tipo, retorna false
            if (!$tipoUsuario || !$tipoUsuario->nome) {
                return false;
            }
            // Compara os Enums (ou seus valores)
            return $tipoUsuario->routePrefix() === $tipo;
            
            // Ou compare pelos valores se preferir:
            // return $tipoUsuario->nome->value === $tipo->value;
=======
    public function isEmpresaTipo(TipoEmpresa|string $tipo): bool
    {
        if (is_string($tipo)) {
            $tipo = TipoEmpresa::tryFrom($tipo);
        }

        return $this->getTipoEmpresa() === $tipo;
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
    }

    /**
     * Retorna a rota do dashboard apropriada para o usuário.
     */
    public function getDashboardRoute(): string
    {
<<<<<<< HEAD

    // Admin master sem empresa vai para dashboard admin geral
        if ($this->cargo_id === 1 && !$this->empresa_id) {
            Log::warning('[getDashboardRoute] Redirecionando para admin.dashboard - Admin master sem empresa', [
                'user_id' => $this->id,
                'email' => $this->email,
                'cargo_id' => $this->cargo_id,
                'empresa_id' => $this->empresa_id,
            ]);
            return 'admin.dashboard';
        }


        // Usuário com empresa vai para dashboard do tipo
        $tipoEmpresa = $this->getTipoEmpresa();

        Log::info('[getDashboardRoute] Verificando tipo de empresa', [
            'user_id' => $this->id,
            'email' => $this->email,
            'cargo_id' => $this->cargo_id,
            'empresa_id' => $this->empresa_id,
            'tipo_empresa' => $tipoEmpresa->nome,
            'empresa_loaded' => $this->relationLoaded('empresa'),
            'empresa_exists' => $this->empresa ? 'SIM' : 'NÃO',
        ]);
        
        if ($tipoEmpresa) {
            $route = $tipoEmpresa->dashboardRoute();
            Log::info('[getDashboardRoute] Dashboard encontrado para tipo', [
                'user_id' => $this->id,
                'email' => $this->email,
                'tipo' => $tipoEmpresa->nome,
                'route' => $route,
            ]);
            return $route;
        }

        // Fallback para cliente sem empresa
        Log::warning('[getDashboardRoute] Nenhum tipo de empresa encontrado - usando fallback', [
            'user_id' => $this->id,
            'email' => $this->email,
            'cargo_id' => $this->cargo_id,
            'empresa_id' => $this->empresa_id,
            'empresa_loaded' => $this->relationLoaded('empresa'),
        ]);
=======
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
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
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
