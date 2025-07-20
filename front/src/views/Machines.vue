<template>
  <v-container class="py-6">
    <v-text-field
      v-model="search"
      label="Rechercher un matériel..."
      prepend-inner-icon="mdi-magnify"
      class="mb-6"
      clearable
    />
    <v-row>
      <v-col cols="12" class="d-flex justify-end mb-4">
        <v-btn v-if="canCreateMachine" color="primary" @click="$router.push('/materiel/create')">
          <v-icon left>mdi-plus</v-icon>Créer un matériel
        </v-btn>
      </v-col>
    </v-row>
    <v-row dense>
      <v-col
        v-for="mat in filteredMateriels"
        :key="mat.id"
        cols="12" sm="6" md="4" lg="3"
      >
        <v-card class="mb-4" elevation="3">
          <v-img :src="getImage(mat.image)" height="160" cover />
          <v-card-title>{{ mat.name }}</v-card-title>
          <v-card-text>
            <p>{{ mat.description }}</p>
            <v-btn color="primary" @click="$router.push(`/materiel/${mat.id}`)">Voir plus</v-btn>
          </v-card-text>
          <v-card-actions>
            <v-btn v-if="mat.canEdit" color="secondary" @click="$router.push(`/materiel/${mat.id}/edit`)">Modifier</v-btn>
            <v-btn color="error" @click="$router.push(`/materiel/${mat.id}/delete`)" v-if="mat.canEdit">Supprimer</v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
    <v-row>
      <v-col>
        <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="3000" location="top">
          {{ snackbar.text }}
        </v-snackbar>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">

import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import type { Machine } from '@/types/Machine'
import { useAuth } from '@/composables/useAuth'

const defaultLogo = '/src/assets/logo.png'

function isValidImageUrl(url: string | undefined | null): boolean {
  if (!url) return false
  // Simple check: starts with http(s) or / (for local public assets)
  return /^https?:\/\//.test(url) || url.startsWith('/public/') || url.startsWith('/src/assets/')
}

function getImage(image: string | undefined | null): string {
  return isValidImageUrl(image) ? image! : defaultLogo
}

const materiels = ref<Machine[]>([])
const authStore = useAuthStore()
const snackbar = ref<{ show: boolean; color: string; text: string }>({ show: false, color: 'success', text: '' })
const showSnackbar = (msg: string, color: 'error'|'success' = 'error') => {
  return { show: true, color: color, text: msg }
}

onMounted(async () => {
  try {
    const response = await authStore.apiRequest('/api/machines', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    })
    if (response.success && response.data) {
      // Normalisation pour éviter les accès à des champs undefined
      materiels.value = (response.data as Machine[]).map(m => ({
        ...m,
        name: m.name ?? '',
        description: m.description ?? '',
        image: m.image ?? '',
        canEdit: canCreateMachine.value || (m.utilisateur?.id === authStore.user?.id) // Autoriser l'édition si l'utilisateur est le créateur
      }))
      console.log('Materiels loaded:', materiels.value)
    } else {
      snackbar.value = showSnackbar(response.message || 'Erreur lors du chargement des matériels')
    }
  } catch (e) {
    snackbar.value = showSnackbar('Erreur lors du chargement des matériels')
  }
})


const { hasRole } = useAuth()
const canCreateMachine = computed(() => hasRole('admin') || hasRole('editor') || true)

const search = ref('')
const filteredMateriels = computed(() => {
  if (!search.value) return materiels.value
  return materiels.value.filter(mat => {
    const name = mat.name ? mat.name.toLowerCase() : ''
    const description = mat.description ? mat.description.toLowerCase() : ''
    return name.includes(search.value.toLowerCase()) || description.includes(search.value.toLowerCase())
  })
})
</script>