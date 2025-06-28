import { describe, it, expect, beforeEach, vi } from 'vitest'

// Mock des variables d'environnement
const mockEnv = {
  VITE_BACKEND_URL: undefined
}

vi.stubGlobal('import.meta', {
  env: mockEnv
})

describe('Config module', () => {
  beforeEach(() => {
    vi.resetModules()
    mockEnv.VITE_BACKEND_URL = undefined
  })

  it('should use default API endpoint when env var is not set', async () => {
    const { apiEndpoint } = await import('@/config')
    
    expect(apiEndpoint).toBe('http://localhost:80')
  })

  it('should use environment variable when set', async () => {
    mockEnv.VITE_BACKEND_URL = 'https://production-api.com'
    
    const { apiEndpoint } = await import('@/config')
    
    expect(apiEndpoint).toBe('https://production-api.com')
  })

  it('should export correct CORS headers', async () => {
    const { corsRequestHeaders } = await import('@/config')
    
    expect(corsRequestHeaders).toEqual({
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'Access-Control-Request-Method': 'GET, POST, PUT, DELETE, OPTIONS',
      'Access-Control-Request-Headers': 'Content-Type, X-CSRF-Token',
    })
  })

  it('should have all required CORS headers', async () => {
    const { corsRequestHeaders } = await import('@/config')
    
    expect(corsRequestHeaders).toHaveProperty('Accept')
    expect(corsRequestHeaders).toHaveProperty('Content-Type')
    expect(corsRequestHeaders).toHaveProperty('Access-Control-Request-Method')
    expect(corsRequestHeaders).toHaveProperty('Access-Control-Request-Headers')
  })

  it('should support common HTTP methods in CORS', async () => {
    const { corsRequestHeaders } = await import('@/config')
    
    const methods = corsRequestHeaders['Access-Control-Request-Method']
    expect(methods).toContain('GET')
    expect(methods).toContain('POST')
    expect(methods).toContain('PUT')
    expect(methods).toContain('DELETE')
    expect(methods).toContain('OPTIONS')
  })

  it('should include CSRF token in allowed headers', async () => {
    const { corsRequestHeaders } = await import('@/config')
    
    const headers = corsRequestHeaders['Access-Control-Request-Headers']
    expect(headers).toContain('X-CSRF-Token')
    expect(headers).toContain('Content-Type')
  })
})
