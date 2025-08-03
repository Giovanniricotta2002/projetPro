<template>
  <v-app>
    <v-container class="py-10 d-flex flex-column align-center">
      <v-card class="pa-6" max-width="900" elevation="3" min-width="900">
        <v-card-title class="headline mb-4">Créer une machine</v-card-title>
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
            <v-btn color="primary" type="submit">Créer</v-btn>
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
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { typesBulle } from '@/config/typesBulle'
import type { InfoMachine } from '@/types/InfoMachine'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const snackbar = ref<{ show: boolean; color: string; text: string }>({ show: false, color: 'success', text: '' })

const form = reactive({
  nom: '',
  image: '',
  description: '',
  bulles: [] as InfoMachine[],
})

const types = typesBulle
const authStore = useAuthStore()

function addBulle() {
  form.bulles.push({ text: '', type: types[0].value } as InfoMachine)
}
function removeBulle(i: number) {
  form.bulles.splice(i, 1)
}
async function save() {
  try {
    const response = await authStore.apiRequest('/api/machines/', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        nom: form.nom,
        description: form.description,
        image: form.image,
        infoMachines: form.bulles,
      }),
    })
    console.log(response, authStore.$id, JSON.stringify({
        nom: form.nom,
        description: form.description,
        image: form.image,
        infoMachines: form.bulles,
      }));
    
    if (!response.success) throw new Error(response.message || 'Erreur lors de la création')
    snackbar.value = { show: true, color: 'success', text: 'Machine créée avec succès !' }
    setTimeout(() => router.push('/materiels'), 1200)
  } catch (e) {
    console.log(e);
    
    snackbar.value = { show: true, color: 'error', text: 'Erreur lors de la création de la machine' }
  }
}
</script>
