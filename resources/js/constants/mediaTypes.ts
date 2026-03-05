/**
 * Media Type Constants
 * Standardizes media type naming across frontend components
 * Maps Portuguese names (used in upload) to English names (used in API endpoints)
 */

export const MEDIA_TYPE_MAP = {
  imagens: 'images',
  videos: 'videos',
  plantas: 'plants',
  autorizacoes: 'authorizations'
} as const

export const MEDIA_TYPES = {
  IMAGE: 'imagens',
  VIDEO: 'videos',
  PLANT: 'plantas',
  AUTHORIZATION: 'autorizacoes'
} as const

export const MEDIA_LIMITS = {
  image: {
    maxSize: 5 * 1024 * 1024, // 5MB
    label: 'Imagem',
    mimes: 'png,jpg,jpeg,webp'
  },
  video: {
    maxSize: 1 * 1024 * 1024 * 1024, // 1GB
    label: 'Vídeo',
    mimes: 'mp4,webm,mov,avi'
  },
  planta: {
    maxSize: 10 * 1024 * 1024, // 10MB
    label: 'Planta',
    mimes: 'pdf,jpg,jpeg,png'
  },
  autorizacao: {
    maxSize: 5 * 1024 * 1024, // 5MB
    label: 'Autorização',
    mimes: 'pdf,jpg,jpeg,png'
  }
} as const

/**
 * Get API endpoint key for media type
 * Example: 'imagens' -> 'images'
 */
export function getMediaTypeKey(type: keyof typeof MEDIA_TYPE_MAP): string {
  return MEDIA_TYPE_MAP[type]
}

/**
 * Normalize input media types to MEDIA_TYPE_MAP keys
 * Converts types from ImovelUploadZone (image, video, planta, autorizacao)
 * to types expected by MEDIA_TYPE_MAP (imagens, videos, plantas, autorizacoes)
 */
export const INPUT_TYPE_TO_MAP_KEY = {
  image: 'imagens',      // ImovelUploadZone sends "image" → normalize to "imagens"
  video: 'videos',       // ImovelUploadZone sends "video" → normalize to "videos"
  planta: 'plantas',     // ImovelUploadZone sends "planta" → normalize to "plantas"
  autorizacao: 'autorizacoes',  // ImovelUploadZone sends "autorizacao" → normalize to "autorizacoes"
  // Also support direct Portuguese names for backwards compatibility
  imagens: 'imagens',
  videos: 'videos',
  plantas: 'plantas',
  autorizacoes: 'autorizacoes'
} as const

/**
 * Normalize input type to a valid MEDIA_TYPE_MAP key
 * @param type - Input type from ImovelUploadZone
 * @returns Normalized type key for MEDIA_TYPE_MAP
 */
export function normalizeMediaType(type: string): keyof typeof MEDIA_TYPE_MAP | null {
  const normalized = INPUT_TYPE_TO_MAP_KEY[type as keyof typeof INPUT_TYPE_TO_MAP_KEY]
  if (normalized && normalized in MEDIA_TYPE_MAP) {
    return normalized as keyof typeof MEDIA_TYPE_MAP
  }
  return null
}

/**
 * Get Portuguese media type name
 * Example: 'image' -> 'imagens'
 */
export function getPortugueseName(type: string): string {
  const entry = Object.entries(MEDIA_TYPES).find(([_, value]) => value === type)
  return entry ? entry[1] : type
}
