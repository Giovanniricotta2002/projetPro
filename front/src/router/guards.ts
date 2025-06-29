// router/guards.ts
import type { NavigationGuardNext, RouteLocationNormalized } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

export async function authGuard(
  to: RouteLocationNormalized,
  from: RouteLocationNormalized,
  next: NavigationGuardNext
) {
  const authStore = useAuthStore()
  
  // Si pas encore initialisé, vérifier l'auth
  if (!authStore.isInitialized) {
    await authStore.checkAuth()
  }
  
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const requiresGuest = to.matched.some(record => record.meta.requiresGuest)
  
  if (requiresAuth && !authStore.isAuthenticated) {
    next({ path: '/login', query: { redirect: to.fullPath } })
  } else if (requiresGuest && authStore.isAuthenticated) {
    next({ path: '/' })
  } else {
    next()
  }
}

export function roleGuard(requiredRole: string) {
  return (
    to: RouteLocationNormalized,
    from: RouteLocationNormalized,
    next: NavigationGuardNext
  ) => {
    const authStore = useAuthStore()
    
    if (!authStore.isAuthenticated) {
      next({ name: 'login' })
      return
    }
    
    if (!authStore.hasRole(requiredRole)) {
      next({ name: 'forbidden' }) // Page 403
      return
    }
    
    next()
  }
}
