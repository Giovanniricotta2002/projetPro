import { describe, it, expect } from 'vitest'
import { apiEndpoint, corsRequestHeaders } from '@/config'

describe('config', () => {
  it('apiEndpoint est une string non vide', () => {
    expect(typeof apiEndpoint).toBe('string')
    expect(apiEndpoint.length).toBeGreaterThan(0)
  })

  it('corsRequestHeaders contient les bons headers', () => {
    expect(corsRequestHeaders).toMatchObject({
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'Access-Control-Request-Method': 'GET, POST, PUT, DELETE, OPTIONS',
      'Access-Control-Request-Headers': 'Content-Type, X-CSRF-Token',
    })
  })
})
