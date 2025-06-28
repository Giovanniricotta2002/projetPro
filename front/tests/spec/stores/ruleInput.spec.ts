import { describe, it, expect, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useRuleInput } from '@/stores/ruleInput'

describe('useRuleInput Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  describe('loginRule', () => {
    it('should validate correct login', () => {
      const store = useRuleInput()
      
      expect(store.loginRule('validlogin')).toBe(true)
      expect(store.loginRule('user123')).toBe(true)
      expect(store.loginRule('a'.repeat(180))).toBe(true)
    })

    it('should reject empty login', () => {
      const store = useRuleInput()
      
      expect(store.loginRule('')).toBe('Login must be at least 3 characters long')
    })

    it('should reject login that is too long', () => {
      const store = useRuleInput()
      const longLogin = 'a'.repeat(181)
      
      expect(store.loginRule(longLogin)).toBe('Login must be less than 180 characters')
    })

    it('should reject short login', () => {
      const store = useRuleInput()
      
      expect(store.loginRule('ab')).toBe('Login must be at least 3 characters long')
    })
  })

  describe('passwordRule', () => {
    it('should validate correct password', () => {
      const store = useRuleInput()
      
      expect(store.passwordRule('password123')).toBe(true)
      expect(store.passwordRule('123456')).toBe(true)
      expect(store.passwordRule('a'.repeat(100))).toBe(true)
    })

    it('should reject short password', () => {
      const store = useRuleInput()
      
      expect(store.passwordRule('12345')).toBe('Password must be at least 6 characters long')
      expect(store.passwordRule('')).toBe('Password must be at least 6 characters long')
      expect(store.passwordRule('abc')).toBe('Password must be at least 6 characters long')
    })
  })

  describe('emailRule', () => {
    it('should validate correct email', () => {
      const store = useRuleInput()
      
      expect(store.emailRule('user@example.com')).toBe(true)
      expect(store.emailRule('test.email@domain.co.uk')).toBe(true)
      expect(store.emailRule('user+tag@example.org')).toBe(true)
    })

    it('should reject invalid email', () => {
      const store = useRuleInput()
      
      expect(store.emailRule('invalid-email')).toBe('Invalid email format')
      expect(store.emailRule('user@')).toBe('Invalid email format')
      expect(store.emailRule('@domain.com')).toBe('Invalid email format')
      expect(store.emailRule('user@domain')).toBe('Invalid email format')
      expect(store.emailRule('user space@domain.com')).toBe('Invalid email format')
    })

    it('should handle empty email', () => {
      const store = useRuleInput()
      
      expect(store.emailRule('')).toBe('Invalid email format')
    })
  })

  describe('urlRule', () => {
    it('should validate correct URL', () => {
      const store = useRuleInput()
      
      expect(store.urlRule('https://example.com')).toBe(true)
      expect(store.urlRule('http://test.co.uk')).toBe(true)
      expect(store.urlRule('https://subdomain.example.com:8080/path')).toBe(true)
      expect(store.urlRule('example.com')).toBe(true)
      expect(store.urlRule('domain.fr/path/to/resource')).toBe(true)
    })

    it('should reject invalid URL', () => {
      const store = useRuleInput()
      
      expect(store.urlRule('invalid-url')).toBe('Invalid URL format')
      expect(store.urlRule('http://')).toBe('Invalid URL format')
      expect(store.urlRule('://example.com')).toBe('Invalid URL format')
      expect(store.urlRule('example')).toBe('Invalid URL format')
      expect(store.urlRule('spaces in url.com')).toBe('Invalid URL format')
    })

    it('should handle empty URL', () => {
      const store = useRuleInput()
      
      expect(store.urlRule('')).toBe('Invalid URL format')
    })
  })

  describe('store state', () => {
    it('should initialize with empty state', () => {
      const store = useRuleInput()
      
      expect(store.$state).toEqual({})
    })

    it('should have all required getters', () => {
      const store = useRuleInput()
      
      expect(typeof store.loginRule).toBe('function')
      expect(typeof store.passwordRule).toBe('function')
      expect(typeof store.emailRule).toBe('function')
      expect(typeof store.urlRule).toBe('function')
    })
  })

  describe('edge cases', () => {
    it('should handle special characters in login', () => {
      const store = useRuleInput()
      
      expect(store.loginRule('user.name')).toBe(true)
      expect(store.loginRule('user_123')).toBe(true)
      expect(store.loginRule('user-name')).toBe(true)
    })

    it('should handle international domains in email', () => {
      const store = useRuleInput()
      
      expect(store.emailRule('user@example.fr')).toBe(true)
      expect(store.emailRule('user@domain.info')).toBe(true)
    })

    it('should handle complex URLs', () => {
      const store = useRuleInput()
      
      expect(store.urlRule('https://api.example.com:443/v1/endpoint?param=value')).toBe(true)
      expect(store.urlRule('ftp://files.domain.com/file.txt')).toBe(false) // FTP not supported by current regex
    })
  })
})
