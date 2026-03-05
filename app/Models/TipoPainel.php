<?php

namespace App\Models;

use App\Enums\TipoEmpresa as TipoEmpresaEnum;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Empresa;
use App\Models\Modulo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class TipoPainel extends BaseModel
{
    protected $connection = 'nexa_admin';

    protected $table = 'tipo_painel';

    protected $fillable = [
        'nome',
        'descricao',
    ];

    protected $casts = [
        'nome' => TipoEmpresaEnum::class,
    ];


    /**
     * Retorna o label amigável do tipo (delegando ao enum).
     */
    public function getTipoLabelAttribute(): string
    {
        return $this->nome?->label() ?? '';
    }

    /**
     * Retorna o slug do tipo (para uso em rotas).
     */
    public function getSlugAttribute(): string
    {
        return $this->nome?->slug() ?? '';
    }

    /**
     * Escopo para filtrar por tipo (aceita enum, slug ou string do valor).
     */
    public function scopeOfTipo(Builder $query, TipoEmpresaEnum|string $tipo): Builder
    {
        // Converte string para Enum se necessário
        if (is_string($tipo)) {
            $tipo = TipoEmpresaEnum::fromSlug($tipo);
        }
        
        if (!$tipo) {
            return $query->whereRaw('1 = 0'); // Retorna query vazia se tipo inválido
        }
            
        return $query->where('nome', $tipo->value);
    }

    /**
     * Escopo para filtrar por slug.
     */
    public function scopePorSlug(Builder $query, string $slug): Builder
    {
        $tipo = TipoEmpresaEnum::fromSlug($slug);
        
        if (!$tipo) {
            return $query->whereRaw('1 = 0');
        }
        
        return $query->where('nome', $tipo->value);
    }

    /**
     * Retorna a rota do dashboard (delega para o enum).
     */
    public function dashboardRoute(): string
    {
        return $this->nome?->dashboardRoute() ?? 'cliente.dashboard';
    }

        public function empresas(): HasMany
    {
        return $this->hasMany(Empresa::class, 'tipo_painel_id', 'id');
    }

    /**
     * Retorna o prefixo de rotas.
     */
    public function routePrefix(): string
    {
        return $this->nome?->routePrefix() ?? '';
    }

    /**
     * Acessor para compatibilidade (caso algum código espere 'tipo').
     */
    public function getTipoAttribute()
    {
        return $this->nome;
    }

    /**
     * Encontra um TipoPainel pelo slug.
     */
    public static function findBySlug(string $slug): ?self
    {
        return self::porSlug($slug)->first();
    }

    public function enumToString(): ?TipoEmpresaEnum
    {
        // O cast já converteu, então apenas retorna o valor
        if ($this->nome instanceof TipoEmpresaEnum) {
            return $this->nome;
        }
        
        // Caso não tenha sido convertido (fallback)
        try {
            return TipoEmpresaEnum::tryFrom($this->nome);
        } catch (\ValueError $e) {
            return null;
        }
    }

    /**
     * Módulos disponíveis para este painel. Pivot painel_modulo está em tenant_credentials,
     * então a query é feita na conexão do Modulo.
     */
    public function modulos()
    {
        $moduloIds = DB::connection('tenant_credentials')
            ->table('painel_modulo')
            ->where('painel_id', $this->id)
            ->pluck('modulo_id');
    
        return Modulo::on('tenant_credentials')->whereIn('id', $moduloIds);
    }
    
    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'painel_user')
                    ->withPivot('configuracoes')
                    ->withTimestamps();
    }
    
    public function permissoes()
    {
        return $this->hasManyThrough(Permissao::class, Modulo::class);
    }
    
    public function temModulo($moduloNome)
    {
        return $this->modulos()->where('nome', $moduloNome)->exists();
    }
}