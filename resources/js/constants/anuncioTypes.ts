/**
 * Tipos de anúncio disponíveis
 * Um anúncio pode estar em uma ou mais plataformas
 */
export const ANUNCIO_TIPOS = {
  ADS_GOOGLE: 'Google_ads',
  INSTAGRAM_ADS: 'Instagram_ads',
  WHATSAPP_CAMPAIGN: 'Whatsapp_campaign',
  SITE_ANUNCIO: 'Site_anuncio',
} as const;

export const ANUNCIO_TIPOS_LABELS = {
  [ANUNCIO_TIPOS.ADS_GOOGLE]: 'Google Ads',
  [ANUNCIO_TIPOS.INSTAGRAM_ADS]: 'Instagram Ads',
  [ANUNCIO_TIPOS.WHATSAPP_CAMPAIGN]: 'Campanha de WhatsApp',
  [ANUNCIO_TIPOS.SITE_ANUNCIO]: 'Anúncio do Site',
} as const;

export type AnuncioTipo = typeof ANUNCIO_TIPOS[keyof typeof ANUNCIO_TIPOS];