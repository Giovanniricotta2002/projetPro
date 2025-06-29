// config/auth.ts
export const AUTH_CONFIG = {
  // Intervalle de vérification de l'authentification (5 minutes)
  CHECK_INTERVAL: 5 * 60 * 1000,
  
  // Timeout pour les requêtes API (30 secondes)
  REQUEST_TIMEOUT: 30 * 1000,
  
  // Nombre de tentatives de retry pour le refresh
  MAX_REFRESH_RETRIES: 3,
  
  // Délai avant retry du refresh (1 seconde)
  REFRESH_RETRY_DELAY: 1000,
  
  // Endpoints API
  ENDPOINTS: {
    LOGIN: '/api/login',
    LOGOUT: '/api/logout',
    REGISTER: '/api/register',
    REFRESH: '/api/refresh',
    ME: '/api/me',
    CHANGE_PASSWORD: '/api/change-password',
    UPDATE_PROFILE: '/api/profile',
  },
  
  // Messages d'erreur
  ERRORS: {
    SESSION_EXPIRED: 'Session expirée, veuillez vous reconnecter',
    NETWORK_ERROR: 'Erreur de connexion au serveur',
    UNAUTHORIZED: 'Accès non autorisé',
    INVALID_CREDENTIALS: 'Identifiants invalides',
  }
} as const

export type AuthEndpoint = keyof typeof AUTH_CONFIG.ENDPOINTS
