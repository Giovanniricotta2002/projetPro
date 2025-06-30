<template>
  <div class="logout-page">
    <div class="logout-container">
      <div class="logout-icon">
        <i class="icon-logout-big">🚪</i>
      </div>
      
      <h1 class="logout-title">Déconnexion en cours...</h1>
      
      <div class="logout-message">
        <p>Vous allez être déconnecté dans quelques instants.</p>
        <div class="logout-spinner">
          <div class="spinner"></div>
        </div>
      </div>
      
      <div class="logout-actions">
        <button @click="cancelLogout" class="btn btn-secondary">
          Annuler
        </button>
        <button @click="confirmLogout" class="btn btn-primary">
          Confirmer la déconnexion
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const cancelLogout = () => {
  router.push('/')
}

const confirmLogout = async () => {
  try {
    await authStore.logout()
    router.push('/login')
  } catch (error) {
    console.error('Erreur lors de la déconnexion:', error)
    // Forcer la déconnexion même en cas d'erreur
    authStore.clearAuth()
    router.push('/login')
  }
}

// Déconnexion automatique après 3 secondes
onMounted(() => {
  const timer = setTimeout(() => {
    confirmLogout()
  }, 3000)
  
  // Nettoyer le timer si le composant est détruit
  return () => clearTimeout(timer)
})
</script>

<style scoped>
.logout-page {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem;
}

.logout-container {
  background: white;
  border-radius: 16px;
  padding: 3rem;
  box-shadow: 0 20px 40px rgba(0,0,0,0.1);
  text-align: center;
  max-width: 400px;
  width: 100%;
}

.logout-icon {
  font-size: 4rem;
  margin-bottom: 1.5rem;
}

.logout-title {
  color: #2c3e50;
  margin-bottom: 1rem;
  font-size: 1.5rem;
  font-weight: 600;
}

.logout-message {
  margin-bottom: 2rem;
  color: #7f8c8d;
}

.logout-spinner {
  display: flex;
  justify-content: center;
  margin-top: 1rem;
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #ecf0f1;
  border-top: 3px solid #3498db;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.logout-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-primary {
  background-color: #e74c3c;
  color: white;
}

.btn-primary:hover {
  background-color: #c0392b;
  transform: translateY(-2px);
}

.btn-secondary {
  background-color: #95a5a6;
  color: white;
}

.btn-secondary:hover {
  background-color: #7f8c8d;
  transform: translateY(-2px);
}

@media (max-width: 480px) {
  .logout-container {
    padding: 2rem;
  }
  
  .logout-actions {
    flex-direction: column;
  }
  
  .btn {
    width: 100%;
  }
}
</style>
