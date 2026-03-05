/**
 * Composable para exportar dados em diferentes formatos
 */

export function useExport() {
  /**
   * Exporta dados para CSV
   */
  function exportToCSV(data: any[], filename: string = 'export.csv') {
    if (!data || data.length === 0) {
      console.warn('Nenhum dado para exportar')
      return
    }

    // Extrai os headers das chaves do primeiro objeto
    const headers = Object.keys(data[0])
    
    // Cria as linhas CSV
    const csvRows = [
      headers.join(','), // Header row
      ...data.map(row => 
        headers.map(header => {
          const value = row[header]
          // Escapa aspas duplas e envolve em aspas se contiver vírgula
          const stringValue = String(value ?? '')
          const escapedValue = stringValue.replace(/"/g, '""')
          return escapedValue.includes(',') || escapedValue.includes('\n')
            ? `"${escapedValue}"`
            : escapedValue
        }).join(',')
      )
    ]

    // Cria o blob e faz download
    const csvString = csvRows.join('\n')
    const blob = new Blob(['\uFEFF' + csvString], { type: 'text/csv;charset=utf-8;' })
    downloadBlob(blob, filename)
  }

  /**
   * Exporta dados para JSON
   */
  function exportToJSON(data: any[], filename: string = 'export.json') {
    if (!data || data.length === 0) {
      console.warn('Nenhum dado para exportar')
      return
    }

    const jsonString = JSON.stringify(data, null, 2)
    const blob = new Blob([jsonString], { type: 'application/json;charset=utf-8;' })
    downloadBlob(blob, filename)
  }

  /**
   * Exporta dados para Excel (formato básico via CSV)
   * Para um Excel real, seria necessário uma biblioteca como xlsx
   */
  function exportToExcel(data: any[], filename: string = 'export.xlsx') {
    // Por enquanto, usa CSV com extensão .xlsx
    // Para um Excel real, considere usar a biblioteca 'xlsx'
    exportToCSV(data, filename.replace('.xlsx', '.csv'))
  }

  /**
   * Helper para fazer download de blob
   */
  function downloadBlob(blob: Blob, filename: string) {
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = filename
    link.click()
    window.URL.revokeObjectURL(url)
  }

  /**
   * Formata dados de imóveis para exportação
   */
  function formatImoveisForExport(imoveis: any[]): any[] {
    return imoveis.map(imovel => ({
      'Código': imovel.codigo || '',
      'Nome': imovel.nome || '',
      'Cidade': imovel.cidade || '',
      'Categoria': imovel.categoria || '',
      'Condição': imovel.condicao || '',
      'Status': imovel.status || '',
      'Valor Venda': imovel.valores?.valor_venda || imovel.valor_venda || '',
      'Valor Locação': imovel.valores?.valor_locacao || imovel.valor_locacao || '',
      'Quartos': imovel.quartos || '',
      'Vagas': imovel.vagas || '',
      'Tem Anúncio': imovel.listings && imovel.listings.length > 0 ? 'Sim' : 'Não',
      'Data Criação': imovel.created_at || '',
    }))
  }

  return {
    exportToCSV,
    exportToJSON,
    exportToExcel,
    formatImoveisForExport,
    downloadBlob,
  }
}
