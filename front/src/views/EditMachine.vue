<template>
  <v-app>
    <v-container class="py-10 d-flex flex-column align-center">
      <v-card class="pa-6" max-width="900" elevation="3" min-width="900">
        <v-card-title class="headline mb-4">Éditer la machine</v-card-title>
        <v-form @submit.prevent="save">
          <v-text-field v-model="form.nom" label="Nom de la machine" required class="mb-4" />
          <v-text-field v-model="form.image" label="URL de l'image" required class="mb-4" />
          <v-text-field v-model="form.description" label="Description" required class="mb-4" />
          <v-img :src="form.image" max-width="200" max-height="120" class="mb-4 mx-auto" v-if="form.image" />
          <h3 class="text-h6 font-weight-bold mb-2">Bulles</h3>
          <v-row>
            <v-col cols="12" v-for="(bulle, i) in form.bulles" :key="bulle.id" class="mb-2">
              <v-row>
                <v-col cols="7">
                  <v-text-field v-model="bulle.text" label="Texte de la bulle" class="mb-2" />
                </v-col>
                <v-col cols="3">
                  <v-select v-model="bulle.type" :items="types" label="Type" class="mb-2" />
                </v-col>
                <v-col cols="2" class="d-flex align-center">
                  <v-btn icon color="error" @click="removeBulle(i)"><v-icon>mdi-delete</v-icon></v-btn>
                </v-col>
              </v-row>
            </v-col>
          </v-row>
          <v-row>
            <v-col>
                <v-btn color="primary" class="mb-4" @click="addBulle">Ajouter une bulle</v-btn>
            </v-col>
          </v-row>
          <v-card-actions class="justify-end">
            <v-btn color="primary" type="submit">Enregistrer</v-btn>
          </v-card-actions>
        </v-form>
      </v-card>
      <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="3000" location="top">
        {{ snackbar.text }}
      </v-snackbar>
    </v-container>
  </v-app>
</template>

<script setup lang="ts">


import { useAuthStore } from '@/stores/auth'
import type { Machine } from '@/types/Machine'

const types = [
  { title: 'Usage', value: 'usage' },
  { title: 'Caractéristique', value: 'carac' },
  { title: 'Confort', value: 'confort' },
  { title: 'Sécurité', value: 'sécurité' },
  { title: 'Autre', value: 'autre' },
]


const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const snackbar = ref<{ show: boolean; color: string; text: string }>({ show: false, color: 'success', text: '' })
const showSnackbar = (msg: string, color: 'error'|'success' = 'error') => {
  return { show: true, color: color, text: msg }
}

const form = reactive({
  nom: '',
  image: '',
  bulles: [] as { id: number; text: string; type: string }[],
})

const machineId = Number(route.params.materielId)

onMounted(async () => {
  try {
    const response = await authStore.apiRequest<Machine>(`/api/machines/${machineId}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    })
    if (response.success && response.data) {
      form.nom = response.data.name || ''
      form.image = response.data.image || ''
      form.bulles = response.data.infoMachines || []
      form.description = response.data.description || ''
    } else {
      snackbar.value = showSnackbar(response.message || 'Erreur lors du chargement')
    }
  } catch (e) {
    snackbar.value = showSnackbar('Erreur lors du chargement de la machine')
  }
})

function addBulle() {
  // Trouve le plus grand id existant et incrémente
  const maxId = form.bulles.reduce((max, b) => Math.max(max, b.id), 0)
  form.bulles.push({ id: maxId + 1, text: '', type: 'usage' })
}
function removeBulle(i: number) {
  form.bulles.splice(i, 1)
}
async function save() {
  try {
    const response = await authStore.apiRequest(`/api/machines/${machineId}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        nom: form.nom,
        image: form.image,
        description: form.description,
        infoMachines: form.bulles,
      }),
    })
    if (!response.success) throw new Error(response.message || 'Erreur lors de la modification')
    snackbar.value = showSnackbar('Machine modifiée avec succès!', 'success')
    setTimeout(() => router.back(), 1200)
  } catch (e) {
    snackbar.value = showSnackbar('Erreur lors de la modification de la machine')
  }
}

</script>

<style scoped>
.v-img {
  border-radius: 12px;
  border: 1px solid #ddd;
}
</style>
