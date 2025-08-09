<template>
  <v-app>
    <v-container class="py-10 d-flex flex-column align-center">
      <v-card class="pa-6" max-width="700" elevation="3" min-width="350">
        <v-card-title class="headline mb-4">Profil utilisateur</v-card-title>
        <v-row>
          <v-col cols="12" class="text-center mb-4">
            <v-avatar size="96" class="mb-2">
              <v-icon size="96">mdi-account-circle</v-icon>
            </v-avatar>
            <div class="text-h6">{{ user?.username || 'Utilisateur inconnu' }}</div>
            <div class="grey--text">{{ user?.mail || 'Email non renseigné' }}</div>
            <v-chip class="ma-2" color="primary" v-for="role in user?.roles || []" :key="role">{{ role }}</v-chip>
            <v-chip
              class="ma-2"
              v-if="user && user.status"
              :color="user.status.color"
              label
            >
              {{ user.status.label }}
            </v-chip>
          </v-col>
        </v-row>
        <v-divider class="my-4" />
        <v-list dense>
          <v-list-item>
            <v-list-item-title>Date d'inscription</v-list-item-title>
            <v-list-item-subtitle>
              {{ user?.dateCreation ? formatDateFr(user.dateCreation) : 'Non renseignée' }}
            </v-list-item-subtitle>
          </v-list-item>
          <v-list-item>
            <v-list-item-title>Dernière visite</v-list-item-title>
            <v-list-item-subtitle>
              {{ user?.lastVisit ? formatDateFr(user.lastVisit) : 'Non renseignée' }}
            </v-list-item-subtitle>
          </v-list-item>
          <v-list-item>
            <v-list-item-title>Statut</v-list-item-title>
            <v-list-item-subtitle>
              <v-chip
                v-if="user && user.status"
                :color="user.status.color"
                class="ma-1"
                label
              >
                {{ user.status.label }}
              </v-chip>
              <span v-else>Non renseigné</span>
            </v-list-item-subtitle>
          </v-list-item>
        </v-list>
        <v-divider class="my-4" />
        <v-list dense>
          <v-list-item>
            <v-list-item-title>Forums Créés</v-list-item-title>
            <v-list-item-subtitle>
              <v-row v-if="user && user.forums && user.forums.length" v-for="forum in forumsTree()" :key="forum.id">
                <v-col cols="12">
                  <span>{{ forum.titre }}</span> <br>
                </v-col>
                <v-col cols="12" v-for="children in forum.children" :key="children.id">
                  <v-col>
                    <span>{{ children.titre }}</span>
                  </v-col>
                </v-col>
              </v-row>
              <span v-else>Aucun forum créé</span>
            </v-list-item-subtitle>
          </v-list-item>
        </v-list>
        <v-divider class="my-4" />
        <!-- <v-btn color="primary" class="mt-2" @click="editProfile">Modifier le profil</v-btn> -->
      </v-card>
    </v-container>
  </v-app>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import type { Utilisateur } from '@/types/Utilisateur'
import { useRouter } from 'vue-router'

import { useAuthStore } from '@/stores/auth'
import { onMounted, computed } from 'vue'

const authStore = useAuthStore()
const user = computed(() => authStore.user)

onMounted(async () => {
  // Vérifie et récupère l'utilisateur connecté si besoin (après refresh)
  await authStore.checkAuth()
})


function formatDateFr(dateStr: string) {
  const date = new Date(dateStr)
  if (isNaN(date.getTime())) return dateStr
  return new Intl.DateTimeFormat('fr-FR', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  }).format(date)
}

const router = useRouter()

// Transforme les forums en structure pour v-treeview
function forumsTree() {
  if (!user.value || !user.value.forums) return []
  return user.value.forums.map(forum => ({
    id: forum.id,
    titre: forum.titre || `Forum #${forum.id}`,
    children: [
      { id: `${forum.id}-desc`, titre: `Description: ${forum.description || 'Aucune'}`, children: [] },
      { id: `${forum.id}-date`, titre: `Créé le: ${forum.dateCreation ? formatDateFr(forum.dateCreation) : 'Non renseigné'}`, children: [] },
    ]
  }))
}

</script>
