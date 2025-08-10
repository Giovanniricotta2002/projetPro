import { describe, it, expect } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useRuleInput } from '@/stores/ruleInput'

describe('useRuleInput', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('loginRule fonctionne correctement', () => {
    const store = useRuleInput()
    expect(store.loginRule('')).toBe('Login must be at least 3 characters long')
    expect(store.loginRule('ab')).toBe(true)
    expect(store.loginRule('a'.repeat(181))).toBe('Login must be less than 180 characters')
    expect(store.loginRule('validLogin')).toBe(true)
  })

  it('passwordRule fonctionne correctement', () => {
    const store = useRuleInput()
    expect(store.passwordRule('')).toBe('Password must be at least 6 characters long')
    expect(store.passwordRule('123')).toBe('Password must be at least 6 characters long')
    expect(store.passwordRule('123456')).toBe(true)
  })

  it('emailRule fonctionne correctement', () => {
    const store = useRuleInput()
    expect(store.emailRule('')).toBe('Invalid email format')
    expect(store.emailRule('notanemail')).toBe('Invalid email format')
    expect(store.emailRule('test@example.com')).toBe(true)
  })

  it('urlRule fonctionne correctement', () => {
    const store = useRuleInput()
    expect(store.urlRule('')).toBe('Invalid URL format')
    expect(store.urlRule('notanurl')).toBe('Invalid URL format')
    expect(store.urlRule('http://example.com')).toBe(true)
    expect(store.urlRule('https://example.com/path')).toBe(true)
    expect(store.urlRule('example.com')).toBe(true)
  })

  it('confirmPasswordRule fonctionne correctement', () => {
    const store = useRuleInput()
    expect(store.confirmPasswordRule('', 'password')).toBe('Confirmation du mot de passe requise')
    expect(store.confirmPasswordRule('abc', 'def')).toBe('Les mots de passe ne correspondent pas')
    expect(store.confirmPasswordRule('password', 'password')).toBe(true)
  })
})
