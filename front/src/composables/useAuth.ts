// composables/useAuth.ts
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

export function useAuth() {
  const authStore = useAuthStore()
  const router = useRouter()

  // États réactifs
  const user = computed(() => authStore.user)
  const isLoading = computed(() => authStore.isLoading)
  const error = computed(() => authStore.error)
  const isAuthenticated = computed(() => authStore.isAuthenticated)
  const isInitialized = computed(() => authStore.isInitialized)

  // Actions avec gestion de navigation
  async function loginAndRedirect(loginData: Parameters<typeof authStore.login>[0], redirectTo?: string) {
    const result = await authStore.login(loginData)
    console.log(result);
    
    if (result.success) {
      // Utiliser l'URL de redirection depuis la query ou la valeur par défaut
      const finalRedirect = redirectTo || router.currentRoute.value.query.redirect as string || '/'
      router.push(finalRedirect)
    }
    
    return result
  }

  async function logoutAndRedirect(redirectTo = '/login') {
    await authStore.logout()
    router.push(redirectTo)
  }

  async function registerAndRedirect(registerData: Parameters<typeof authStore.register>[0], redirectTo = '/login') {
    const result = await authStore.register(registerData)
    
    if (result.success) {
      router.push(redirectTo)
    }
    
    return result
  }

  // Guard pour les routes protégées
  function requireAuth(redirectTo = '/login') {
    if (!authStore.isAuthenticated && authStore.isInitialized) {
      router.push(redirectTo)
      return false
    }
    return true
  }

  // Guard pour les routes publiques (ex: login quand déjà connecté)
  function requireGuest(redirectTo = '/') {
    if (authStore.isAuthenticated) {
      router.push(redirectTo)
      return false
    }
    return true
  }

  return {
    // État
    user,
    isLoading,
    error,
    isAuthenticated,
    isInitialized,
    
    // Getters
    userName: computed(() => authStore.userName),
    userEmail: computed(() => authStore.userEmail),
    hasRole: authStore.hasRole,
    
    // Actions de base
    login: authStore.login,
    logout: authStore.logout,
    register: authStore.register,
    checkAuth: authStore.checkAuth,
    updateProfile: authStore.updateProfile,
    changePassword: authStore.changePassword,
    clearError: authStore.clearError,
    
    // Actions avec navigation
    loginAndRedirect,
    logoutAndRedirect,
    registerAndRedirect,
    
    // Guards
    requireAuth,
    requireGuest,
    
    // Nouvelles fonctions pour cookies HTTPOnly et refresh
    refreshToken: authStore.refreshToken,
    startAuthCheck: authStore.startAuthCheck,
    stopAuthCheck: authStore.stopAuthCheck,
    forceLogout: authStore.forceLogout,
  }
}
