import { describe, it, expect, vi, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useCSRFToken } from '@/stores/useCSRFToken'

global.fetch = vi.fn()

describe('useCSRFToken', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('a un état initial correct', () => {
    const store = useCSRFToken()
    expect(store.token).toBe('')
    expect(store.loading).toBe(false)
    expect(store.error).toBeNull()
  })

  it('fetchCSRFToken met à jour le token en cas de succès', async () => {
    const store = useCSRFToken()
    ;(global.fetch as any).mockResolvedValueOnce({
      ok: true,
      json: async () => ({ csrfToken: 'abc123' }),
    })
    await store.fetchCSRFToken()
    expect(store.token).toBe('abc123')
    expect(store.loading).toBe(false)
    expect(store.error).toBeNull()
  })

  it('fetchCSRFToken gère les erreurs réseau', async () => {
    const store = useCSRFToken()
    ;(global.fetch as any).mockResolvedValueOnce({
      ok: false,
      json: async () => ({}),
    })
    await store.fetchCSRFToken()
    expect(store.token).toBe('')
    expect(store.loading).toBe(false)
    expect(store.error).toBe('Failed to fetch CSRF token')
  })

  it('fetchCSRFToken gère les exceptions', async () => {
    const store = useCSRFToken()
    ;(global.fetch as any).mockRejectedValueOnce(new Error('Network error'))
    await store.fetchCSRFToken()
    expect(store.token).toBe('')
    expect(store.loading).toBe(false)
    expect(store.error).toBe('Network error')
  })
})
