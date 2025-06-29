// composables/useNetworkError.ts
import { ref, computed, readonly } from 'vue'

export function useNetworkError() {
  const networkError = ref<string>('')
  const isOnline = ref(navigator.onLine)
  
  // Écouter les changements de connexion
  window.addEventListener('online', () => {
    isOnline.value = true
    networkError.value = ''
  })
  
  window.addEventListener('offline', () => {
    isOnline.value = false
    networkError.value = 'Pas de connexion internet'
  })
  
  const hasNetworkError = computed(() => !!networkError.value)
  
  function setNetworkError(error: string) {
    networkError.value = error
  }
  
  function clearNetworkError() {
    networkError.value = ''
  }
  
  return {
    networkError: readonly(networkError),
    isOnline: readonly(isOnline),
    hasNetworkError,
    setNetworkError,
    clearNetworkError,
  }
}
