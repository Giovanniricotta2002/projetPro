import { describe, it, expect } from 'vitest'
import { typesBulle } from '@/config/typesBulle'

describe('typesBulle', () => {
  it('contient 5 types', () => {
    expect(typesBulle).toHaveLength(5)
  })

  it('contient les bons titres et valeurs', () => {
    expect(typesBulle).toEqual([
      { title: 'Usage', value: 'usage' },
      { title: 'Caractéristique', value: 'carac' },
      { title: 'Confort', value: 'confort' },
      { title: 'Sécurité', value: 'sécurité' },
      { title: 'Autre', value: 'autre' },
    ])
  })

  it('chaque type a un title et un value', () => {
    for (const type of typesBulle) {
      expect(type).toHaveProperty('title')
      expect(type).toHaveProperty('value')
      expect(typeof type.title).toBe('string')
      expect(typeof type.value).toBe('string')
    }
  })
})
