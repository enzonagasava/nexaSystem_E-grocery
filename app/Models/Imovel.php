<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Imovel extends Model
{
    protected $connection = 'tenant_content';
    protected $table = 'imoveis';

    protected $fillable = [
        'user_id',
        'codigo',
        'nome',
        'descricao',
        'status',
        'categoria',
        'finalidade',
        'modalidade',
        'condicao',
        'exclusividade',
        'cep',
        'estado',
        'cidade',
        'bairro',
        'endereco',
        'numero',
        'complemento',
        'referencia',
        'mostrar_endereco_completo',
        'valor_venda',
        'valor_locacao',
        'valor_condominio',
        'valor_iptu',
        'aceita_financiamento',
        'aceita_permuta',
        'comissao_percent',
        'comissao_valor',
        'area_total',
        'area_construida',
        'area_util',
        'area_terreno_largura',
        'area_terreno_comprimento',
        'quartos',
        'suites',
        'banheiros',
        'vagas',
        'salas',
        'andar',
        'ano_construcao',
        'mobilia',
        'itens',
        'areas_lazer',
        'varanda',
        'gas',
        'luz',
        'proprietario_nome',
        'proprietario_telefone',
        'proprietario_email',
        'proprietario_documento',
        'autorizacao_venda'
    ];

    protected $casts = [
        'exclusividade' => 'boolean',
        'mostrar_endereco_completo' => 'boolean',
        'aceita_financiamento' => 'boolean',
        'aceita_permuta' => 'boolean',
        'autorizacao_venda' => 'boolean',
        'itens' => 'array',
        'valor_venda' => 'decimal:2',
        'valor_locacao' => 'decimal:2',
        'valor_condominio' => 'decimal:2',
        'valor_iptu' => 'decimal:2',
        'comissao_percent' => 'decimal:2',
        'comissao_valor' => 'decimal:2',
        'area_total' => 'decimal:2',
        'area_construida' => 'decimal:2',
        'area_util' => 'decimal:2',
        'area_terreno_largura' => 'decimal:2',
        'area_terreno_comprimento' => 'decimal:2',
    ];

    /**
     * Scope para aplicar filtros dinâmicos na listagem de imóveis.
     * 
     * Este scope permite filtrar imóveis por diversos critérios de forma
     * reutilizável e modular. Pode ser usado em qualquer controller/query.
     * 
     * @param Builder $query
     * @param array $filters Array associativo com os filtros a aplicar
     * @return Builder
     * 
     * Filtros suportados:
     * - q: busca por texto livre (nome, descrição)
     * - cidade: filtra por cidade exata
     * - condicao: filtra por condição (venda, locação, etc)
     * - categoria: filtra por categoria
     * - status: filtra por status
     * - min_valor: valor mínimo de venda
     * - max_valor: valor máximo de venda
     * - has_listing: true/false para imóveis com ou sem anúncio
     * - min_quartos: número mínimo de quartos
     * - min_vagas: número mínimo de vagas
     * - sort: campo para ordenação (created_at, valor_venda, valor_locacao, nome)
     * - order: direção da ordenação (asc, desc)
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        // Busca por texto livre (nome ou descrição)
        if (!empty($filters['q'])) {
            $searchTerm = $filters['q'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nome', 'like', "%{$searchTerm}%")
                  ->orWhere('descricao', 'like', "%{$searchTerm}%")
                  ->orWhere('codigo', 'like', "%{$searchTerm}%");
            });
        }

        // Filtro por cidade
        if (!empty($filters['cidade'])) {
            $query->where('cidade', 'like', "%{$filters['cidade']}%");
        }

        // Filtro por condição (venda, locação, etc)
        if (!empty($filters['condicao'])) {
            $query->where('condicao', $filters['condicao']);
        }

        // Filtro por categoria
        if (!empty($filters['categoria'])) {
            $query->where('categoria', $filters['categoria']);
        }

        // Filtro por status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Faixa de preço (valor de venda)
        if (isset($filters['min_valor']) && is_numeric($filters['min_valor'])) {
            $query->where('valor_venda', '>=', $filters['min_valor']);
        }

        if (isset($filters['max_valor']) && is_numeric($filters['max_valor'])) {
            $query->where('valor_venda', '<=', $filters['max_valor']);
        }

        // Filtro por imóveis com ou sem anúncio (listing)
        if (isset($filters['has_listing'])) {
            // Converte string para boolean se necessário
            $hasListing = $filters['has_listing'];
            if (is_string($hasListing)) {
                $hasListing = $hasListing === 'true' || $hasListing === '1';
            }
            
            if ($hasListing) {
                // Se houver um mínimo de anúncios, filtra por quantidade
                if (isset($filters['min_listings']) && is_numeric($filters['min_listings']) && (int)$filters['min_listings'] > 1) {
                    $min = (int) $filters['min_listings'];
                    $query->whereHas('listings', function ($q) {
                        // no extra constraints inside relation
                    }, '>=', $min);
                } else {
                    $query->whereHas('listings');
                }
            } else {
                $query->whereDoesntHave('listings');
            }
        }

        // Filtro por número mínimo de quartos
        if (isset($filters['min_quartos']) && is_numeric($filters['min_quartos'])) {
            $query->where('quartos', '>=', $filters['min_quartos']);
        }

        // Filtro por número mínimo de vagas
        if (isset($filters['min_vagas']) && is_numeric($filters['min_vagas'])) {
            $query->where('vagas', '>=', $filters['min_vagas']);
        }

        // Ordenação customizada
        if (!empty($filters['sort'])) {
            $sortField = $filters['sort'];
            $sortOrder = !empty($filters['order']) && strtolower($filters['order']) === 'asc' ? 'asc' : 'desc';
            $query->orderBy($sortField, $sortOrder);
        }

        return $query;
    }

    public function imagens(): HasMany
    {
        return $this->hasMany(ImovelImagem::class, 'imovel_id')->orderBy('ordem');
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'imovel_id');
    }

    public function midias()
    {
        return $this->hasMany(ImovelMidia::class, 'imovel_id')->orderBy('ordem');
    }

    public function plantas()
    {
        return $this->hasMany(ImovelPlanta::class, 'imovel_id');
    }

    public function videos()
    {
        return $this->hasMany(ImovelVideo::class, 'imovel_id');
    }

    public function autorizacoes()
    {
        return $this->hasMany(ImovelAutorizacao::class, 'imovel_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtém valor vendido por dia no período especificado
     * 
     * @param array $userIds IDs dos usuários a filtrar
     * @param int $dias Número de dias para consultar
     * @return \Illuminate\Support\Collection
     */
    public static function getValorVendidoPeriodo(array $userIds, int $dias): \Illuminate\Support\Collection
    {
        return self::whereIn('user_id', $userIds)
            ->where('status', 'vendido')
            ->where('updated_at', '>=', Carbon::now()->subDays($dias))
            ->selectRaw('DATE(updated_at) as data, SUM(valor_venda) as valor')
            ->groupBy('data')
            ->orderBy('data')
            ->get()
            ->map(fn($item) => [
                'data' => Carbon::parse($item->data)->format('d/m'),
                'valor' => (float) $item->valor ?? 0,
            ]);
    }

    /**
     * Obtém distribuição de imóveis vendidos por categoria
     * 
     * @param array $userIds IDs dos usuários a filtrar
     * @param int $dias Número de dias para consultar
     * @return \Illuminate\Support\Collection
     */
    public static function getImoveisVendidosPorCategoria(array $userIds, int $dias): \Illuminate\Support\Collection
    {
        return self::whereIn('user_id', $userIds)
            ->where('status', 'vendido')
            ->where('updated_at', '>=', Carbon::now()->subDays($dias))
            ->selectRaw('categoria as nome, COUNT(*) as quantidade')
            ->groupBy('categoria')
            ->orderByDesc('quantidade')
            ->get()
            ->map(fn($item) => [
                'nome' => $item->nome ?? 'Sem Categoria',
                'quantidade' => (int) $item->quantidade,
            ]);
    }

    /**
     * Obtém comissões por dia no período especificado
     * 
     * @param array $userIds IDs dos usuários a filtrar
     * @param int $dias Número de dias para consultar
     * @return \Illuminate\Support\Collection
     */
    public static function getComissoesPeriodo(array $userIds, int $dias): \Illuminate\Support\Collection
    {
        return self::whereIn('user_id', $userIds)
            ->where('status', 'vendido')
            ->whereNotNull('comissao_valor')
            ->where('comissao_valor', '>', 0)
            ->where('updated_at', '>=', Carbon::now()->subDays($dias))
            ->selectRaw('DATE(updated_at) as data, SUM(comissao_valor) as total')
            ->groupBy('data')
            ->orderBy('data')
            ->get()
            ->map(fn($item) => [
                'data' => Carbon::parse($item->data)->format('d/m'),
                'total' => (float) $item->total ?? 0,
            ]);
    }

    /**
     * Obtém o total de vendas em um período
     * 
     * @param array $userIds IDs dos usuários a filtrar
     * @return float
     */
    public static function getVendasTotais(array $userIds): float
    {
        return self::whereIn('user_id', $userIds)
            ->where('status', 'vendido')
            ->sum('valor_venda') ?? 0;
    }

    /**
     * Obtém quantidade de imóveis ativos
     * 
     * @param array $userIds IDs dos usuários a filtrar
     * @return int
     */
    public static function getImoveisAtivos(array $userIds): int
    {
        return self::whereIn('user_id', $userIds)
            ->whereIn('status', ['disponivel', 'ativo'])
            ->count();
    }

    /**
     * Obtém quantidade de vendas fechadas
     * 
     * @param array $userIds IDs dos usuários a filtrar
     * @return int
     */
    public static function getVendasFechadas(array $userIds): int
    {
        return self::whereIn('user_id', $userIds)
            ->where('status', 'vendido')
            ->count();
    }

    /**
     * Obtém faturamento (valor_venda + valor_locacao) por dia no período especificado
     * 
     * @param array $userIds IDs dos usuários a filtrar
     * @param int $dias Número de dias para consultar
     * @return \Illuminate\Support\Collection
     */
    public static function getFaturamentoPeriodo(array $userIds, int $dias): \Illuminate\Support\Collection
    {
        return self::whereIn('user_id', $userIds)
            ->where('status', 'vendido')
            ->where('updated_at', '>=', Carbon::now()->subDays($dias))
            ->selectRaw('DATE(updated_at) as data, SUM(valor_venda + COALESCE(valor_locacao, 0)) as total')
            ->groupBy('data')
            ->orderBy('data')
            ->get()
            ->map(fn($item) => [
                'data' => Carbon::parse($item->data)->format('d/m'),
                'total' => (float) $item->total ?? 0,
            ]);
    }

    /**
     * Obtém faturamento total (valor_venda + valor_locacao) em um período
     * 
     * @param array $userIds IDs dos usuários a filtrar
     * @return float
     */
    public static function getFaturamentoTotal(array $userIds): float
    {
        return self::whereIn('user_id', $userIds)
            ->where('status', 'vendido')
            ->selectRaw('SUM(valor_venda + COALESCE(valor_locacao, 0)) as total')
            ->first()
            ->total ?? 0;
    }

    /**
     * Obtém valor de locação por dia no período especificado
     * 
     * @param array $userIds IDs dos usuários a filtrar
     * @param int $dias Número de dias para consultar
     * @return \Illuminate\Support\Collection
     */
    public static function getLocacaoPeriodo(array $userIds, int $dias): \Illuminate\Support\Collection
    {
        return self::whereIn('user_id', $userIds)
            ->whereNotNull('valor_locacao')
            ->where('valor_locacao', '>', 0)
            ->where('updated_at', '>=', Carbon::now()->subDays($dias))
            ->selectRaw('DATE(updated_at) as data, SUM(valor_locacao) as total')
            ->groupBy('data')
            ->orderBy('data')
            ->get()
            ->map(fn($item) => [
                'data' => Carbon::parse($item->data)->format('d/m'),
                'total' => (float) $item->total ?? 0,
            ]);
    }

    /**
     * Obtém quantidade de locações por dia no período especificado
     *
     * @param array $userIds IDs dos usuários a filtrar
     * @param int $dias Número de dias para consultar
     * @return \Illuminate\Support\Collection
     */
    public static function getLocacoesCountPeriodo(array $userIds, int $dias): \Illuminate\Support\Collection
    {
        return self::whereIn('user_id', $userIds)
            ->whereNotNull('valor_locacao')
            ->where('valor_locacao', '>', 0)
            ->where('updated_at', '>=', Carbon::now()->subDays($dias))
            ->selectRaw('DATE(updated_at) as data, COUNT(*) as quantidade')
            ->groupBy('data')
            ->orderBy('data')
            ->get()
            ->map(fn($item) => [
                'data' => Carbon::parse($item->data)->format('d/m'),
                'quantidade' => (int) ($item->quantidade ?? 0),
            ]);
    }

    /**
     * Obtém quantidade total de locações
     * 
     * @param array $userIds IDs dos usuários a filtrar
     * @return int
     */
    public static function getLocacoesTotal(array $userIds): int
    {
        return self::whereIn('user_id', $userIds)
            ->whereNotNull('valor_locacao')
            ->where('valor_locacao', '>', 0)
            ->count();
    }

    /**
     * Obtém valor total de locação
     * 
     * @param array $userIds IDs dos usuários a filtrar
     * @return float
     */
    public static function getValorLocacaoTotal(array $userIds): float
    {
        return self::whereIn('user_id', $userIds)
            ->whereNotNull('valor_locacao')
            ->where('valor_locacao', '>', 0)
            ->sum('valor_locacao') ?? 0;
    }
}
