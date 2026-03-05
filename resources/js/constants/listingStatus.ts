/**
 * Mapeamento de status de anúncio para labels em português
 */
export const LISTING_STATUS = [
  {
    value: 'rascunho',
    label: 'Rascunho',
    color: 'var(--muted)',
    description: 'Anúncio não publicado'
  },
  {
    value: 'publicado',
    label: 'Publicado',
    color: 'var(--primary)',
    description: 'Anúncio ativo e visível'
  },
  {
    value: 'pausado',
    label: 'Pausado',
    color: 'var(--warning)',
    description: 'Anúncio temporariamente pausado'
  },
  {
    value: 'finalizado',
    label: 'Finalizado',
    color: 'var(--destructive)',
    description: 'Anúncio encerrado'
  },
]

/**
 * Obtém o label de status de anúncio
 * @param value - Valor do status do banco de dados
 * @returns Objeto com label, color e description, ou null se não encontrado
 */
export function getListingStatusLabel(value: string | null | undefined): (typeof LISTING_STATUS[number]) | null {
  if (!value) return null
  return LISTING_STATUS.find(s => s.value === value) || null
}

/**
 * Obtém apenas o label de texto
 */
export function getListingStatusText(value: string | null | undefined): string {
  const status = getListingStatusLabel(value)
  return status?.label || (value ? value.charAt(0).toUpperCase() + value.slice(1) : '—')
}
