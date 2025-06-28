import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount, VueWrapper } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import Login from '@/views/Login.vue'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

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

const vuetify = createVuetify({
  components,
  directives,
})

// Mock du localStorage
const localStorageMock = {
  getItem: vi.fn(),
  setItem: vi.fn(),
  removeItem: vi.fn(),
  clear: vi.fn()
}
Object.defineProperty(window, 'localStorage', { value: localStorageMock })

global.ResizeObserver = require('resize-observer-polyfill')

describe('Login Component', () => {
  let wrapper: VueWrapper<any>
  let router: any
  let pinia: any

  beforeEach(() => {
    pinia = createPinia()
    setActivePinia(pinia)
    
    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: '/', component: { template: '<div>Home</div>' } },
        { path: '/register', component: { template: '<div>Register</div>' } }
      ]
    })

    mockFetch.mockClear()
    localStorageMock.setItem.mockClear()
    localStorageMock.getItem.mockClear()
  })

  afterEach(() => {
    if (wrapper) {
      wrapper.unmount()
    }
  })

  const mountComponent = () => {
    return mount(Login, {
      global: {
        plugins: [vuetify, pinia, router],
      }
    })
  }

  it('should render login form', () => {
    wrapper = mountComponent()
    
    expect(wrapper.find('input[name="login"]').exists()).toBe(true)
    expect(wrapper.find('input[name="password"]').exists()).toBe(true)
    expect(wrapper.find('button[type="submit"]').exists()).toBe(true)
    expect(wrapper.text()).toContain('Se connecter')
  })

  it('should display title and version', () => {
    wrapper = mountComponent()
    
    expect(wrapper.text()).toContain('Se connecter')
    expect(wrapper.text()).toContain('0.0.1')
  })

  it('should fetch CSRF token on mount', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: vi.fn().mockResolvedValueOnce({
        csrfToken: 'test-token'
      })
    })

    wrapper = mountComponent()
    await wrapper.vm.$nextTick()

    expect(mockFetch).toHaveBeenCalledWith(
      new URL('/api/csrfToken', 'http://test-api.com'),
      expect.objectContaining({
        method: 'GET',
        credentials: 'include'
      })
    )
  })

  it('should handle successful login', async () => {
    // Mock CSRF token fetch
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: vi.fn().mockResolvedValueOnce({
        csrfToken: 'test-csrf-token'
      })
    })

    // Mock login request
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: vi.fn().mockResolvedValueOnce({
        success: true,
        message: 'Login successful'
      })
    })

    const routerPushSpy = vi.spyOn(router, 'push')

    wrapper = mountComponent()
    await wrapper.vm.$nextTick()

    // Fill form
    const loginInput = wrapper.find('input[name="login"]')
    const passwordInput = wrapper.find('input[name="password"]')
    
    await loginInput.setValue('testuser')
    await passwordInput.setValue('password123')

    // Submit form
    await wrapper.find('form').trigger('submit.prevent')
    await wrapper.vm.$nextTick()

    expect(localStorageMock.setItem).toHaveBeenCalledWith('token', 'test-csrf-token')
    expect(routerPushSpy).toHaveBeenCalledWith('/')
  })

  it('should handle login error', async () => {
    // Mock CSRF token fetch
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: vi.fn().mockResolvedValueOnce({
        csrfToken: 'test-csrf-token'
      })
    })

    // Mock failed login request
    mockFetch.mockResolvedValueOnce({
      ok: false,
      json: vi.fn().mockResolvedValueOnce({
        message: 'Invalid credentials'
      })
    })

    wrapper = mountComponent()
    await wrapper.vm.$nextTick()

    // Fill form
    const loginInput = wrapper.find('input[name="login"]')
    const passwordInput = wrapper.find('input[name="password"]')
    
    await loginInput.setValue('testuser')
    await passwordInput.setValue('wrongpassword')

    // Submit form
    await wrapper.find('form').trigger('submit.prevent')
    await wrapper.vm.$nextTick()

    expect(wrapper.find('.v-alert--type-error').exists()).toBe(true)
    expect(wrapper.text()).toContain('Invalid credentials')
  })

  it('should redirect to register page', async () => {
    const routerPushSpy = vi.spyOn(router, 'push')

    wrapper = mountComponent()
    
    const registerButton = wrapper.findAll('button').find(btn => 
      btn.text().includes('Cree un compte')
    )
    
    await registerButton?.trigger('click')

    expect(routerPushSpy).toHaveBeenCalledWith('/register')
  })

  it('should validate form inputs', async () => {
    wrapper = mountComponent()
    
    const loginInput = wrapper.find('input[name="login"]')
    const passwordInput = wrapper.find('input[name="password"]')
    
    // Test empty inputs
    await loginInput.setValue('')
    await passwordInput.setValue('')
    await loginInput.trigger('blur')
    await passwordInput.trigger('blur')

    expect(wrapper.text()).toContain('Login must be at least 3 characters long')
    expect(wrapper.text()).toContain('Password must be at least 6 characters long')
  })

  it('should disable submit button when form is invalid', async () => {
    wrapper = mountComponent()
    
    const submitButton = wrapper.findAll('button').find(btn => 
      btn.text().includes('Se connecter')
    )
    
    // Form should be invalid initially (empty inputs)
    expect(submitButton?.attributes('disabled')).toBeDefined()
    
    // Fill valid inputs
    const loginInput = wrapper.find('input[name="login"]')
    const passwordInput = wrapper.find('input[name="password"]')
    
    await loginInput.setValue('validuser')
    await passwordInput.setValue('validpassword')
    await wrapper.vm.$nextTick()

    // Button should be enabled with valid inputs
    expect(submitButton?.attributes('disabled')).toBeUndefined()
  })

  it('should handle network error during login', async () => {
    // Mock CSRF token fetch
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: vi.fn().mockResolvedValueOnce({
        csrfToken: 'test-csrf-token'
      })
    })

    // Mock network error
    mockFetch.mockRejectedValueOnce(new Error('Network error'))

    wrapper = mountComponent()
    await wrapper.vm.$nextTick()

    // Fill form
    const loginInput = wrapper.find('input[name="login"]')
    const passwordInput = wrapper.find('input[name="password"]')
    
    await loginInput.setValue('testuser')
    await passwordInput.setValue('password123')

    // Submit form
    await wrapper.find('form').trigger('submit.prevent')
    await wrapper.vm.$nextTick()

    expect(wrapper.find('.v-alert--type-error').exists()).toBe(true)
    expect(wrapper.text()).toContain('Network error')
  })

  it('should include CSRF token in login request', async () => {
    const testToken = 'test-csrf-token-123'
    
    // Mock CSRF token fetch
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: vi.fn().mockResolvedValueOnce({
        csrfToken: testToken
      })
    })

    // Mock login request
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: vi.fn().mockResolvedValueOnce({
        success: true
      })
    })

    wrapper = mountComponent()
    await wrapper.vm.$nextTick()

    // Fill form
    const loginInput = wrapper.find('input[name="login"]')
    const passwordInput = wrapper.find('input[name="password"]')
    
    await loginInput.setValue('testuser')
    await passwordInput.setValue('password123')

    // Submit form
    await wrapper.find('form').trigger('submit.prevent')
    await wrapper.vm.$nextTick()

    // Check that CSRF token was included in the request
    expect(mockFetch).toHaveBeenCalledWith(
      new URL('/api/login', 'http://test-api.com'),
      expect.objectContaining({
        method: 'POST',
        headers: expect.objectContaining({
          'X-CSRF-Token': testToken
        }),
        body: JSON.stringify({
          login: 'testuser',
          password: 'password123',
          'X-CSRF-Token': testToken
        })
      })
    )
  })
})
