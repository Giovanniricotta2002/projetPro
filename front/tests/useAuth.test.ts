import { describe, it, expect, vi, beforeEach } from 'vitest'
import { useAuth } from '@/composables/useAuth'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import { nextTick } from 'vue'

vi.mock('vue-router', () => {
  const push = vi.fn()
  return {
    useRouter: () => ({
      push,
      currentRoute: { value: { query: {} } },
    }),
  }
})

vi.mock('@/stores/auth', () => {
  return {
    useAuthStore: vi.fn(),
  }
})

describe('useAuth composable', () => {
  let authStoreMock: any
  let routerMock: any

  beforeEach(() => {
    setActivePinia(createPinia())
    routerMock = useRouter()
    if (routerMock.push && 'mockClear' in routerMock.push) {
      routerMock.push.mockClear()
    }
    authStoreMock = {
      user: { id: 1, name: 'Test' },
      isLoading: false,
      error: null,
      isAuthenticated: false,
      isInitialized: true,
      userName: 'Test',
      userEmail: 'test@example.com',
      hasRole: vi.fn(),
      login: vi.fn().mockResolvedValue({ success: true }),
      logout: vi.fn().mockResolvedValue(undefined),
      register: vi.fn().mockResolvedValue({ success: true }),
      checkAuth: vi.fn(),
      updateProfile: vi.fn(),
      changePassword: vi.fn(),
      clearError: vi.fn(),
      refreshToken: vi.fn(),
      startAuthCheck: vi.fn(),
      stopAuthCheck: vi.fn(),
      forceLogout: vi.fn(),
    }
    ;(useAuthStore as any).mockReturnValue(authStoreMock)
  })

  it('expose l\'état et les getters', () => {
    const auth = useAuth()
    expect(auth.user.value).toEqual({ id: 1, name: 'Test' })
    expect(auth.isLoading.value).toBe(false)
    expect(auth.error.value).toBeNull()
    expect(auth.isAuthenticated.value).toBe(false)
    expect(auth.isInitialized.value).toBe(true)
    expect(auth.userName.value).toBe('Test')
    expect(auth.userEmail.value).toBe('test@example.com')
    expect(auth.hasRole).toBe(authStoreMock.hasRole)
  })

  it('loginAndRedirect appelle login et router.push', async () => {
    const auth = useAuth()
    const result = await auth.loginAndRedirect({ username: 'a', password: 'b' }, '/foo')
    expect(authStoreMock.login).toHaveBeenCalledWith({ username: 'a', password: 'b' })
    expect(routerMock.push).toHaveBeenCalledWith('/foo')
    expect(result.success).toBe(true)
  })

  it('logoutAndRedirect appelle logout et router.push', async () => {
    const auth = useAuth()
    await auth.logoutAndRedirect('/bye')
    expect(authStoreMock.logout).toHaveBeenCalled()
    expect(routerMock.push).toHaveBeenCalledWith('/bye')
  })

  it('registerAndRedirect appelle register et router.push', async () => {
    const auth = useAuth()
    const result = await auth.registerAndRedirect({ username: 'a', password: 'b' }, '/welcome')
    expect(authStoreMock.register).toHaveBeenCalledWith({ username: 'a', password: 'b' })
    expect(routerMock.push).toHaveBeenCalledWith('/welcome')
    expect(result.success).toBe(true)
  })

  it('requireAuth redirige si non authentifié', () => {
    const auth = useAuth()
    authStoreMock.isAuthenticated = false
    authStoreMock.isInitialized = true
    const result = auth.requireAuth('/login')
    expect(routerMock.push).toHaveBeenCalledWith('/login')
    expect(result).toBe(false)
  })

  it('requireAuth ne redirige pas si authentifié', () => {
    const auth = useAuth()
    authStoreMock.isAuthenticated = true
    authStoreMock.isInitialized = true
    const result = auth.requireAuth('/login')
    expect(routerMock.push).not.toHaveBeenCalled()
    expect(result).toBe(true)
  })

  it('requireGuest redirige si authentifié', () => {
    const auth = useAuth()
    authStoreMock.isAuthenticated = true
    const result = auth.requireGuest('/')
    expect(routerMock.push).toHaveBeenCalledWith('/')
    expect(result).toBe(false)
  })

  it('requireGuest ne redirige pas si non authentifié', () => {
    const auth = useAuth()
    authStoreMock.isAuthenticated = false
    const result = auth.requireGuest('/')
    expect(routerMock.push).not.toHaveBeenCalled()
    expect(result).toBe(true)
  })

  it('expose les actions de base et avancées', () => {
    const auth = useAuth()
    expect(auth.login).toBe(authStoreMock.login)
    expect(auth.logout).toBe(authStoreMock.logout)
    expect(auth.register).toBe(authStoreMock.register)
    expect(auth.checkAuth).toBe(authStoreMock.checkAuth)
    expect(auth.updateProfile).toBe(authStoreMock.updateProfile)
    expect(auth.changePassword).toBe(authStoreMock.changePassword)
    expect(auth.clearError).toBe(authStoreMock.clearError)
    expect(auth.refreshToken).toBe(authStoreMock.refreshToken)
    expect(auth.startAuthCheck).toBe(authStoreMock.startAuthCheck)
    expect(auth.stopAuthCheck).toBe(authStoreMock.stopAuthCheck)
    expect(auth.forceLogout).toBe(authStoreMock.forceLogout)
  })
})
