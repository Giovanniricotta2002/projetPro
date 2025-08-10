import { describe, it, expect, vi, beforeEach } from 'vitest'
import { roleGuard } from '@/router/guards'
import { useAuthStore } from '@/stores/auth'

vi.mock('@/stores/auth', () => ({
  useAuthStore: vi.fn(),
}))

describe('roleGuard', () => {
  let authStoreMock: any
  let next: any

  beforeEach(() => {
    next = vi.fn()
    authStoreMock = {
      isAuthenticated: false,
      hasRole: vi.fn(),
    }
    ;(useAuthStore as any).mockReturnValue(authStoreMock)
  })

  it('redirige vers login si non authentifié', () => {
    const guard = roleGuard('admin')
    guard({} as any, {} as any, next)
    expect(next).toHaveBeenCalledWith({ name: 'login' })
  })

  it('redirige vers / si authentifié mais pas le bon rôle', () => {
    authStoreMock.isAuthenticated = true
    authStoreMock.hasRole.mockReturnValue(false)
    const guard = roleGuard('admin')
    guard({} as any, {} as any, next)
    expect(next).toHaveBeenCalledWith({ path: '/' })
  })

  it('laisse passer si authentifié et a le bon rôle', () => {
    authStoreMock.isAuthenticated = true
    authStoreMock.hasRole.mockImplementation(role => role === 'admin')
    const guard = roleGuard('admin')
    guard({} as any, {} as any, next)
    expect(next).toHaveBeenCalledWith()
  })

  it('accepte si au moins un des rôles requis', () => {
    authStoreMock.isAuthenticated = true
    authStoreMock.hasRole.mockImplementation(role => role === 'user')
    const guard = roleGuard('admin', 'user')
    guard({} as any, {} as any, next)
    expect(next).toHaveBeenCalledWith()
  })

  it('redirige vers / si aucun des rôles requis', () => {
    authStoreMock.isAuthenticated = true
    authStoreMock.hasRole.mockReturnValue(false)
    const guard = roleGuard('admin', 'user')
    guard({} as any, {} as any, next)
    expect(next).toHaveBeenCalledWith({ path: '/' })
  })
})
