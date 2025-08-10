import { describe, it, expect, vi, beforeEach } from 'vitest'
import { useNetworkError } from '@/composables/useNetworkError'

// Mock window.addEventListener and navigator.onLine
globalThis.addEventListener = vi.fn()
Object.defineProperty(globalThis, 'navigator', {
  value: { onLine: true },
  writable: true,
})

describe('useNetworkError', () => {
  beforeEach(() => {
    // Reset mocks and navigator state
    (globalThis.addEventListener as any).mockClear()
    globalThis.navigator.onLine = true
  })

  it('initialise isOnline selon navigator.onLine', () => {
    globalThis.navigator.onLine = true
    const { isOnline } = useNetworkError()
    expect(isOnline.value).toBe(true)
    globalThis.navigator.onLine = false
    const { isOnline: isOnline2 } = useNetworkError()
    expect(isOnline2.value).toBe(false)
  })

  it('setNetworkError et clearNetworkError fonctionnent', () => {
    const { networkError, setNetworkError, clearNetworkError, hasNetworkError } = useNetworkError()
    expect(networkError.value).toBe('')
    expect(hasNetworkError.value).toBe(false)
    setNetworkError('Erreur réseau')
    expect(networkError.value).toBe('Erreur réseau')
    expect(hasNetworkError.value).toBe(true)
    clearNetworkError()
    expect(networkError.value).toBe('')
    expect(hasNetworkError.value).toBe(false)
  })

  it('réagit aux événements online/offline', () => {
    let onlineHandler: (() => void) | undefined
    let offlineHandler: (() => void) | undefined
    ;(globalThis.addEventListener as any) = vi.fn((event, cb) => {
      if (event === 'online') onlineHandler = cb
      if (event === 'offline') offlineHandler = cb
    })
    globalThis.navigator.onLine = false
    const { isOnline, networkError } = useNetworkError()
    expect(isOnline.value).toBe(false)
    // networkError n'est pas encore défini car l'événement n'a pas été déclenché
    expect(networkError.value).toBe('')
    // Simule la perte de connexion
    if (offlineHandler) offlineHandler()
    expect(isOnline.value).toBe(false)
    expect(networkError.value).toBe('Pas de connexion internet')
    // Simule le retour en ligne
    if (onlineHandler) onlineHandler()
    expect(isOnline.value).toBe(true)
    expect(networkError.value).toBe('')
  })
})
