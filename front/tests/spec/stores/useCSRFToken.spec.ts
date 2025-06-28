import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useCSRFToken } from '@/stores/useCSRFToken'

// Mock de la configuration
vi.mock('@/config', () => ({
  apiEndpoint: 'http://test-api.com',
  corsRequestHeaders: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  }
}))

// Mock de fetch global
const mockFetch = vi.fn()
global.fetch = mockFetch

describe('useCSRFToken Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    mockFetch.mockClear()
  })

  it('should initialize with default state', () => {
    const store = useCSRFToken()
    
    expect(store.token).toBe('')
    expect(store.loading).toBe(false)
    expect(store.error).toBe(null)
  })

  it('should fetch CSRF token successfully', async () => {
    const mockToken = 'test-csrf-token-123'
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: vi.fn().mockResolvedValueOnce({
        csrfToken: mockToken
      })
    })

    const store = useCSRFToken()
    await store.fetchCSRFToken()

    expect(store.token).toBe(mockToken)
    expect(store.loading).toBe(false)
    expect(store.error).toBe(null)
    
    expect(mockFetch).toHaveBeenCalledWith(
      new URL('/api/csrfToken', 'http://test-api.com'),
      {
        method: 'GET',
        credentials: 'include',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        }
      }
    )
  })

  it('should handle fetch error with HTTP error status', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: false
    })

    const store = useCSRFToken()
    await store.fetchCSRFToken()

    expect(store.token).toBe('')
    expect(store.loading).toBe(false)
    expect(store.error).toBe('Failed to fetch CSRF token')
  })

  it('should handle network error', async () => {
    const networkError = new Error('Network error')
    mockFetch.mockRejectedValueOnce(networkError)

    const store = useCSRFToken()
    await store.fetchCSRFToken()

    expect(store.token).toBe('')
    expect(store.loading).toBe(false)
    expect(store.error).toBe('Network error')
  })

  it('should handle unknown error', async () => {
    mockFetch.mockRejectedValueOnce('Unknown error')

    const store = useCSRFToken()
    await store.fetchCSRFToken()

    expect(store.token).toBe('')
    expect(store.loading).toBe(false)
    expect(store.error).toBe('An unknown error occurred')
  })

  it('should set loading state during fetch', async () => {
    let resolvePromise: (value: any) => void
    const promise = new Promise((resolve) => {
      resolvePromise = resolve
    })

    mockFetch.mockReturnValueOnce(promise)

    const store = useCSRFToken()
    const fetchPromise = store.fetchCSRFToken()

    // Loading should be true during fetch
    expect(store.loading).toBe(true)
    expect(store.error).toBe(null)

    // Resolve the promise
    resolvePromise!({
      ok: true,
      json: vi.fn().mockResolvedValueOnce({ csrfToken: 'token' })
    })

    await fetchPromise

    // Loading should be false after fetch
    expect(store.loading).toBe(false)
  })

  it('should handle empty token response', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: vi.fn().mockResolvedValueOnce({})
    })

    const store = useCSRFToken()
    await store.fetchCSRFToken()

    expect(store.token).toBe('')
    expect(store.loading).toBe(false)
    expect(store.error).toBe(null)
  })

  it('should handle malformed JSON response', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: vi.fn().mockRejectedValueOnce(new Error('Invalid JSON'))
    })

    const store = useCSRFToken()
    await store.fetchCSRFToken()

    expect(store.token).toBe('')
    expect(store.loading).toBe(false)
    expect(store.error).toBe('Invalid JSON')
  })
})
