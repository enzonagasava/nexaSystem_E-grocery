import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

export interface ImovelFilters {
  q?: string
  cidade?: string
  condicao?: string
  categoria?: string
  status?: string
  min_listings?: number | null
  min_valor?: number | null
  max_valor?: number | null
  has_listing?: boolean | null
  min_quartos?: number | null
  min_vagas?: number | null
  sort?: string
  order?: 'asc' | 'desc'
}

/**
 * Composable para gerenciar filtros de imóveis de forma reutilizável.
 * 
 * Sincroniza o estado dos filtros com a URL via Inertia.js e fornece
 * métodos para aplicar, limpar e resetar filtros.
 * 
 * @param routeName - Nome da rota Inertia onde aplicar os filtros
 * @param initialFilters - Filtros iniciais (geralmente vindos de props/query params)
 */
export function useFilters(routeName: string, initialFilters: Partial<ImovelFilters> = {}) {
  // Estado dos filtros
  const filters = ref<ImovelFilters>({
    q: initialFilters.q || '',
    cidade: initialFilters.cidade || '',
    condicao: initialFilters.condicao || '',
    categoria: initialFilters.categoria || '',
    status: initialFilters.status || '',
    min_valor: initialFilters.min_valor ?? null,
    min_listings: initialFilters.min_listings ?? null,
    max_valor: initialFilters.max_valor ?? null,
    has_listing: initialFilters.has_listing ?? null,
    min_quartos: initialFilters.min_quartos ?? null,
    min_vagas: initialFilters.min_vagas ?? null,
    sort: initialFilters.sort || '',
    order: initialFilters.order || 'desc',
  })

  // Indicador de carregamento
  const isLoading = ref(false)

  /**
   * Verifica se há filtros ativos (qualquer valor diferente do padrão)
   */
  const hasActiveFilters = computed(() => {
    return !!(
      filters.value.q ||
      filters.value.cidade ||
      filters.value.condicao ||
      filters.value.categoria ||
      filters.value.status ||
      filters.value.min_valor !== null ||
      filters.value.min_listings !== null ||
      filters.value.max_valor !== null ||
      filters.value.has_listing !== null ||
      filters.value.min_quartos !== null ||
      filters.value.min_vagas !== null
    )
  })

  /**
   * Conta quantos filtros estão ativos
   */
  const activeFiltersCount = computed(() => {
    let count = 0
    if (filters.value.q) count++
    if (filters.value.cidade) count++
    if (filters.value.condicao) count++
    if (filters.value.categoria) count++
    if (filters.value.status) count++
    if (filters.value.min_valor !== null) count++
    if (filters.value.min_listings !== null) count++
    if (filters.value.max_valor !== null) count++
    if (filters.value.has_listing !== null) count++
    if (filters.value.min_quartos !== null) count++
    if (filters.value.min_vagas !== null) count++
    return count
  })

  /**
   * Retorna apenas os filtros com valores definidos (remove nulls/empties)
   */
  const activeFilters = computed(() => {
    const cleaned: Record<string, any> = {}
    
    Object.entries(filters.value).forEach(([key, value]) => {
      if (value !== null && value !== undefined && value !== '') {
        // Converte has_listing string para boolean se necessário
        if (key === 'has_listing') {
          if (value === 'true' || value === true) cleaned[key] = true
          else if (value === 'false' || value === false) cleaned[key] = false
        } else {
          cleaned[key] = value
        }
      }
    })
    
    return cleaned
  })

  /**
   * Aplica os filtros fazendo uma navegação Inertia
   * Preserva os filtros na URL via query params e mantém histórico
   */
  function applyFilters(options: { preserveScroll?: boolean } = {}) {
    isLoading.value = true
    
    router.visit(route(routeName), {
      data: activeFilters.value,
      preserveState: true,
      preserveScroll: options.preserveScroll ?? true,
      only: ['produtos'], // Apenas recarrega os produtos, não a página inteira
      onFinish: () => {
        isLoading.value = false
      },
    })
  }

  /**
   * Limpa todos os filtros e recarrega a listagem sem filtros
   */
  function clearFilters() {
    filters.value = {
      q: '',
      cidade: '',
      condicao: '',
      categoria: '',
      status: '',
      min_valor: null,
      max_valor: null,
      has_listing: null,
      min_quartos: null,
      min_vagas: null,
      sort: '',
      order: 'desc',
    }
    
    applyFilters({ preserveScroll: false })
  }

  /**
   * Reseta os filtros para os valores iniciais
   */
  function resetFilters() {
    filters.value = {
      q: initialFilters.q || '',
      cidade: initialFilters.cidade || '',
      condicao: initialFilters.condicao || '',
      categoria: initialFilters.categoria || '',
      status: initialFilters.status || '',
      min_valor: initialFilters.min_valor ?? null,
      max_valor: initialFilters.max_valor ?? null,
      has_listing: initialFilters.has_listing ?? null,
      min_quartos: initialFilters.min_quartos ?? null,
      min_vagas: initialFilters.min_vagas ?? null,
      sort: initialFilters.sort || '',
      order: initialFilters.order || 'desc',
    }
  }

  /**
   * Atualiza um filtro específico
   */
  function updateFilter<K extends keyof ImovelFilters>(key: K, value: ImovelFilters[K]) {
    filters.value[key] = value
  }

  /**
   * Salva os filtros atuais como favoritos no localStorage
   */
  function saveFavoriteFilters(name: string) {
    const favorites = getFavoriteFilters()
    favorites[name] = { ...activeFilters.value }
    localStorage.setItem(`filters_favorites_${routeName}`, JSON.stringify(favorites))
  }

  /**
   * Carrega filtros favoritos salvos
   */
  function getFavoriteFilters(): Record<string, Record<string, any>> {
    const saved = localStorage.getItem(`filters_favorites_${routeName}`)
    return saved ? JSON.parse(saved) : {}
  }

  /**
   * Aplica um filtro favorito
   */
  function applyFavoriteFilter(name: string) {
    const favorites = getFavoriteFilters()
    if (favorites[name]) {
      Object.assign(filters.value, favorites[name])
      applyFilters()
    }
  }

  /**
   * Remove um filtro favorito
   */
  function removeFavoriteFilter(name: string) {
    const favorites = getFavoriteFilters()
    delete favorites[name]
    localStorage.setItem(`filters_favorites_${routeName}`, JSON.stringify(favorites))
  }

  return {
    filters,
    hasActiveFilters,
    activeFiltersCount,
    activeFilters,
    isLoading,
    applyFilters,
    clearFilters,
    resetFilters,
    updateFilter,
    saveFavoriteFilters,
    getFavoriteFilters,
    applyFavoriteFilter,
    removeFavoriteFilter,
  }
}
