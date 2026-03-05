import { ref, computed } from 'vue'
import axios from 'axios'

export function useCategoriaAutocomplete() {
  const searchTerm = ref('')
  const isLoading = ref(false)
  const suggestions = ref<string[]>([])
  const allCategorias = ref<string[]>([])

  /**
   * Carrega todas as categorias disponíveis no banco
   */
  async function fetchAllCategorias(): Promise<void> {
    if (allCategorias.value.length > 0) {
      suggestions.value = allCategorias.value
      return
    }

    try {
      isLoading.value = true
      const response = await axios.get(route('admin.corretor.imoveis.categorias'))
      
      allCategorias.value = response.data || []
      suggestions.value = allCategorias.value
    } catch (error) {
      console.error('Erro ao buscar categorias:', error)
      suggestions.value = []
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Filtra localmente as categorias com busca case-insensitive
   */
  function filterLocalCategorias(term: string): string[] {
    if (!term || term.length < 1) {
      return allCategorias.value
    }

    const normalizedTerm = term.toLowerCase()
    return allCategorias.value.filter((categoria: string) =>
      categoria.toLowerCase().includes(normalizedTerm)
    )
  }

  /**
   * Busca categorias cadastradas no banco de dados (apenas categorias com imóveis)
   */
  async function searchCategorias(term: string): Promise<void> {
    // Se não há termo, mostra todas
    if (!term || term.length < 1) {
      await fetchAllCategorias()
      return
    }

    try {
      isLoading.value = true
      const response = await axios.get(route('admin.corretor.imoveis.categorias'), {
        params: { q: term }
      })
      
      suggestions.value = response.data || []
    } catch (error) {
      console.error('Erro ao buscar categorias:', error)
      suggestions.value = []
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Filtra categorias baseado no termo de busca
   */
  const filteredCategorias = computed(() => suggestions.value)

  /**
   * Atualiza termo de busca e filtra resultados
   */
  function updateSearch(term: string) {
    searchTerm.value = term
    searchCategorias(term)
  }

  /**
   * Limpa a busca
   */
  function clearSearch() {
    searchTerm.value = ''
    suggestions.value = []
  }

  return {
    searchTerm,
    isLoading,
    filteredCategorias,
    suggestions,
    updateSearch,
    clearSearch,
    fetchAllCategorias,
    filterLocalCategorias,
  }
}
