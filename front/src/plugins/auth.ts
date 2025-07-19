// plugins/auth.ts
import { useAuthStore } from '@/stores/auth'

export async function initAuth() {
  const authStore = useAuthStore()
  
  // Vérifier l'authentification au démarrage
  // await authStore.checkAuth()
  
  // // Si authentifié, démarrer la vérification périodique
  // if (authStore.isAuthenticated) {
  //   authStore.startAuthCheck()
  // }
}

// Plugin pour Vue
export default {
  install() {
    // L'initialisation sera appelée dans main.ts
  }
}
