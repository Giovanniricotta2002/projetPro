<template>
  <v-container class="py-6">
    <v-text-field
      v-model="search"
      label="Rechercher un matériel..."
      prepend-inner-icon="mdi-magnify"
      class="mb-6"
      clearable
    />
    <v-row dense>
      <v-col
        v-for="mat in filteredMateriels"
        :key="mat.id"
        cols="12" sm="6" md="4" lg="3"
      >
        <v-card class="mb-4" elevation="3">
          <v-img :src="mat.image" height="160" cover v-if="mat.image" />
          <v-card-title>{{ mat.nom }}</v-card-title>
          <v-card-text>
            <p>{{ mat.description }}</p>
            <v-btn color="primary" @click="$router.push(`/materiel/${mat.id}`)">Voir plus</v-btn>
          </v-card-text>
          <v-card-actions>
            <v-btn v-if="mat.canEdit" color="secondary" @click="$router.push(`/materiel/${mat.id}/edit`)">Modifier</v-btn>
            <!-- <v-btn color="error" @click="$router.push(`/materiel/${mat.id}/delete`)" v-if="mat.canEdit">Supprimer</v-btn> -->
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

// Simulation : l'API doit renvoyer canEdit selon le voter Symfony
const materiels = ref([
  ...Array.from({ length: 30 }, (_, i) => ({
    id: i + 1,
    nom: `Matériel ${i + 1}`,
    description: `Description du matériel ${i + 1}. Ce matériel est utilisé pour...`,
    image: i % 3 === 0 ? '/src/assets/logo.png' : '',
    canEdit: i % 4 !== 0 // Simule le droit d'édition (à remplacer par la vraie donnée API)
  }))
])

const search = ref('')
const filteredMateriels = computed(() => {
  if (!search.value) return materiels.value
  return materiels.value.filter(mat =>
    mat.nom.toLowerCase().includes(search.value.toLowerCase()) ||
    mat.description.toLowerCase().includes(search.value.toLowerCase())
  )
})
</script>