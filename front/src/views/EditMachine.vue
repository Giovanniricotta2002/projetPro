<template>
  <v-app>
    <v-container class="py-10 d-flex flex-column align-center">
      <v-card class="pa-6" max-width="900" elevation="3" min-width="900">
        <v-card-title class="headline mb-4">Éditer la machine</v-card-title>
        <v-form @submit.prevent="save">
          <v-text-field v-model="form.nom" label="Nom de la machine" required class="mb-4" />
          <v-text-field v-model="form.image" label="URL de l'image" required class="mb-4" />
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
    </v-container>
  </v-app>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'

const types = [
  { title: 'Usage', value: 'usage' },
  { title: 'Caractéristique', value: 'carac' },
  { title: 'Confort', value: 'confort' },
  { title: 'Sécurité', value: 'sécurité' },
  { title: 'Autre', value: 'autre' },
]

const router = useRouter()

const form = reactive({
  nom: 'Haltère hexagonale',
  image: 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fimages.ctfassets.net%2Fipjoepkmtnha%2F5fQCLXzP2H5BG5VcODED43%2Fc3d521f38437d3181c1705bb9979568f%2Fhex-dumbbell_hero&f=1&nofb=1&ipt=15dc359e6340d1aa00a421d911774013ca9723ab3180ca199263745a24cd805f',
  bulles: [
    { id: 1, text: 'Musculation variée', type: 'usage' },
    { id: 2, text: 'Poids variable', type: 'carac' },
  ]
})

function addBulle() {
  // Trouve le plus grand id existant et incrémente
  const maxId = form.bulles.reduce((max, b) => Math.max(max, b.id), 0)
  form.bulles.push({ id: maxId + 1, text: '', type: 'usage' })
}
function removeBulle(i: number) {
  form.bulles.splice(i, 1)
}
function save() {
  // Ici tu peux envoyer form à l'API ou faire un emit
  alert('Machine enregistrée ! (simulation)')
//   router.back()
}
</script>

<style scoped>
.v-img {
  border-radius: 12px;
  border: 1px solid #ddd;
}
</style>
