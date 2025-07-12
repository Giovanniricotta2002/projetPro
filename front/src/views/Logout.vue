<template>
  <v-container class="d-flex flex-column align-center justify-center" style="min-height: 60vh;">
    <v-progress-circular indeterminate color="primary" size="64" class="mb-4" />
    <div>Déconnexion en cours...</div>
  </v-container>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'

const router = useRouter()
const { logout } = useAuth()

onMounted(async () => {
  await logout()
  router.replace('/login')
})
</script>
