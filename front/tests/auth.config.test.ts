import { describe, it, expect } from 'vitest'
import { AUTH_CONFIG } from '@/config/auth'

describe('AUTH_CONFIG', () => {
  it('a les bonnes valeurs numériques', () => {
    expect(AUTH_CONFIG.CHECK_INTERVAL).toBe(5 * 60 * 1000)
    expect(AUTH_CONFIG.REQUEST_TIMEOUT).toBe(30 * 1000)
    expect(AUTH_CONFIG.MAX_REFRESH_RETRIES).toBe(3)
    expect(AUTH_CONFIG.REFRESH_RETRY_DELAY).toBe(1000)
  })

  it('contient tous les endpoints attendus', () => {
    expect(AUTH_CONFIG.ENDPOINTS).toEqual({
      LOGIN: '/api/login',
      LOGOUT: '/api/logout',
      REGISTER: '/api/register',
      REFRESH: '/api/refresh',
      ME: '/api/me',
      CHANGE_PASSWORD: '/api/change-password',
      UPDATE_PROFILE: '/api/profile',
    })
  })

  it('contient tous les messages d\'erreur attendus', () => {
    expect(AUTH_CONFIG.ERRORS).toEqual({
      SESSION_EXPIRED: 'Session expirée, veuillez vous reconnecter',
      NETWORK_ERROR: 'Erreur de connexion au serveur',
      UNAUTHORIZED: 'Accès non autorisé',
      INVALID_CREDENTIALS: 'Identifiants invalides',
    })
  })

})
