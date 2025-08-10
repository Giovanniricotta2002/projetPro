import { describe, it, expect } from 'vitest'
import routes from '@/router/routes'

describe('routes', () => {
  it('contient toutes les routes principales attendues', () => {
    const names = routes.map(r => r.name)
    expect(names).toEqual(
      expect.arrayContaining([
        'Accueil', 'login', 'register', 'forum', 'posts', 'discussion',
        'materiels', 'materiel_edit', 'materiel', 'logout', 'admin',
        'materiel_create', 'profil'
      ])
    )
  })

  it('chaque route a un composant et un meta', () => {
    for (const route of routes) {
      expect(route).toHaveProperty('component')
      expect(route).toHaveProperty('meta')
    }
  })

  it('la route /admin nécessite l\'authentification et le flag admin', () => {
    const adminRoute = routes.find(r => r.name === 'admin')
    expect(adminRoute).toBeDefined()
    expect(adminRoute?.meta.requiresAuth).toBe(true)
    expect(adminRoute?.meta.admin).toBe(true)
  })

  it('la route /login n\'exige pas l\'authentification', () => {
    const loginRoute = routes.find(r => r.name === 'login')
    expect(loginRoute).toBeDefined()
    expect(loginRoute?.meta.requiresGuest).toBe(false)
  })

  it('aucune route ne possède de beforeEnter actif', () => {
    for (const route of routes) {
      expect(route.beforeEnter).toBeUndefined()
    }
  })
})
