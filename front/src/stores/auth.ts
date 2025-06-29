// stores/auth.ts
import { defineStore } from 'pinia'
import { ref, computed, watch, readonly } from 'vue'
import { apiEndpoint, corsRequestHeaders } from '../config'
import type { ApiResponse, LoginData, RegisterData, User } from '../types/types'

export const useAuthStore = defineStore('auth', () => {
  // État
  const user = ref<User | null>(null)
  const isLoading = ref(false)
  const error = ref('')
  const isInitialized = ref(false)

  // Getters
  const isAuthenticated = computed(() => !!user.value && isInitialized.value)
  const userName = computed(() => user.value?.username || '')
  const userEmail = computed(() => user.value?.email || '')
  const hasRole = computed(() => (role: string) => user.value?.roles?.includes(role) ?? false)

  // Watcher pour nettoyer les erreurs automatiquement
  watch([user, isLoading], () => {
    if (user.value || isLoading.value) {
      error.value = ''
    }
  })

  // Utilitaire pour les requêtes API
  async function apiRequest<T>(
    endpoint: string, 
    options: RequestInit = {}
  ): Promise<ApiResponse<T>> {
    try {
      const url = new URL(endpoint, apiEndpoint)
      const response = await fetch(url, {
        credentials: 'include',
        headers: {
          ...corsRequestHeaders,
          'Content-Type': 'application/json',
          ...options.headers,
        },
        ...options,
      })

      const data = await response.json()

      if (!response.ok) {
        throw new Error(data.message || `Erreur ${response.status}`)
      }

      return {
        success: true,
        data,
        message: data.message,
      }
    } catch (err: any) {
      return {
        success: false,
        message: err.message || 'Une erreur est survenue',
      }
    }
  }

  // Actions
  async function login(loginData: LoginData) {
    if (isLoading.value) return { success: false, error: 'Opération en cours...' }
    
    isLoading.value = true
    error.value = ''
    
    const result = await apiRequest<User>('/api/login', {
      method: 'POST',
      headers: {
        'X-CSRF-Token': loginData.csrfToken,
      },
      body: JSON.stringify({
        login: loginData.login.trim(),
        password: loginData.password,
        'X-CSRF-Token': loginData.csrfToken,
      })
    })

    if (result.success && result.data) {
      user.value = result.data
      isInitialized.value = true
    } else {
      error.value = result.message || 'Erreur de connexion'
    }

    isLoading.value = false
    return result
  }

  async function logout() {
    if (isLoading.value) return

    isLoading.value = true
    
    // Appel API de déconnexion (optionnel selon votre backend)
    await apiRequest('/api/logout', {
      method: 'POST',
    })

    // Nettoyage de l'état local
    user.value = null
    error.value = ''
    isInitialized.value = true
    isLoading.value = false
  }

  async function checkAuth() {
    if (isInitialized.value) return isAuthenticated.value

    isLoading.value = true
    
    const result = await apiRequest<User>('/api/me')

    if (result.success && result.data) {
      user.value = result.data
    } else {
      user.value = null
    }

    isInitialized.value = true
    isLoading.value = false
    
    return isAuthenticated.value
  }

  async function register(registerData: RegisterData) {
    if (isLoading.value) return { success: false, error: 'Opération en cours...' }

    isLoading.value = true
    error.value = ''

    const result = await apiRequest('/api/register', {
      method: 'POST',
      headers: {
        'X-CSRF-Token': registerData.csrfToken,
      },
      body: JSON.stringify({
        username: registerData.username.trim(),
        mail: registerData.email.trim().toLowerCase(),
        password: registerData.password,
        'X-CSRF-Token': registerData.csrfToken,
      })
    })

    if (!result.success) {
      error.value = result.message || 'Erreur lors de la création du compte'
    }

    isLoading.value = false
    return result
  }

  async function updateProfile(profileData: Partial<User>) {
    if (isLoading.value || !user.value) return { success: false, error: 'Non authentifié' }

    isLoading.value = true
    error.value = ''

    const result = await apiRequest<User>('/api/profile', {
      method: 'PUT',
      body: JSON.stringify(profileData)
    })

    if (result.success && result.data) {
      user.value = { ...user.value, ...result.data }
    } else {
      error.value = result.message || 'Erreur lors de la mise à jour'
    }

    isLoading.value = false
    return result
  }

  async function changePassword(currentPassword: string, newPassword: string, csrfToken: string) {
    if (isLoading.value) return { success: false, error: 'Opération en cours...' }

    isLoading.value = true
    error.value = ''

    const result = await apiRequest('/api/change-password', {
      method: 'POST',
      headers: {
        'X-CSRF-Token': csrfToken,
      },
      body: JSON.stringify({
        currentPassword,
        newPassword,
        'X-CSRF-Token': csrfToken,
      })
    })

    if (!result.success) {
      error.value = result.message || 'Erreur lors du changement de mot de passe'
    }

    isLoading.value = false
    return result
  }

  // Utilitaire pour nettoyer les erreurs
  function clearError() {
    error.value = ''
  }

  // Utilitaire pour reset complet du store
  function $reset() {
    user.value = null
    isLoading.value = false
    error.value = ''
    isInitialized.value = false
  }

  return {
    // État
    user: readonly(user),
    isLoading: readonly(isLoading),
    error: readonly(error),
    isInitialized: readonly(isInitialized),
    
    // Getters
    isAuthenticated,
    userName,
    userEmail,
    hasRole,
    
    // Actions
    login,
    logout,
    checkAuth,
    register,
    updateProfile,
    changePassword,
    clearError,
    $reset,
  }
})
