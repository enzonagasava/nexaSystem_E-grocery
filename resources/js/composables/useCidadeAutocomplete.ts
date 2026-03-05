import { ref, computed } from 'vue'
import axios from 'axios'

/**
 * Remove acentos de uma string para comparação insensível a acentos
 */
function removeAccents(str: string): string {
  return str
    .normalize('NFD')
    .replace(/[\p{M}]/gu, '')
    .toLowerCase()
}

export function useCidadeAutocomplete() {
  const searchTerm = ref('')
  const isLoading = ref(false)
  const suggestions = ref<string[]>([])
  const allCidades = ref<string[]>([])

  /**
   * Carrega todas as cidades disponíveis no banco
   */
  async function fetchAllCidades(): Promise<void> {
    if (allCidades.value.length > 0) {
      suggestions.value = allCidades.value
      return
    }

    try {
      isLoading.value = true
      const response = await axios.get(route('admin.corretor.imoveis.cidades'))
      
      allCidades.value = response.data || []
      suggestions.value = allCidades.value
    } catch (error) {
      console.error('Erro ao buscar cidades:', error)
      suggestions.value = []
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Filtra localmente as cidades com busca insensível a acentos
   */
  function filterLocalCidades(term: string): string[] {
    if (!term || term.length < 2) {
      return allCidades.value
    }

    const normalizedTerm = removeAccents(term)
    return allCidades.value.filter((cidade: string) =>
      removeAccents(cidade).includes(normalizedTerm)
    )
  }

  /**
   * Busca cidades cadastradas no banco de dados (apenas cidades com imóveis)
   */
  async function searchCidades(term: string): Promise<void> {
    // Se não há termo, mostra todas
    if (!term || term.length < 2) {
      await fetchAllCidades()
      return
    }

    try {
      isLoading.value = true
      const response = await axios.get(route('admin.corretor.imoveis.cidades'), {
        params: { q: term }
      })
      
      suggestions.value = response.data || []
    } catch (error) {
      console.error('Erro ao buscar cidades:', error)
      suggestions.value = []
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Filtra cidades baseado no termo de busca
   */
  const filteredCidades = computed(() => suggestions.value)

  /**
   * Atualiza termo de busca e filtra resultados
   */
  function updateSearch(term: string) {
    searchTerm.value = term
    searchCidades(term)
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
    filteredCidades,
    suggestions,
    updateSearch,
    clearSearch,
    fetchAllCidades,
    filterLocalCidades,
  }
}
