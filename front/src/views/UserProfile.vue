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
            <div class="text-h6">{{ user.username }}</div>
            <div class="grey--text">{{ user.mail || 'Email non renseigné' }}</div>
            <v-chip class="ma-2" color="primary" v-for="role in user.roles" :key="role">{{ role }}</v-chip>
            <v-chip class="ma-2" :color="statusColor(user.status)">{{ user.status }}</v-chip>
          </v-col>
        </v-row>
        <v-divider class="my-4" />
        <v-list dense>
          <v-list-item>
            <v-list-item-title>Date d'inscription</v-list-item-title>
            <v-list-item-subtitle>{{ user.dateCreation || 'Non renseignée' }}</v-list-item-subtitle>
          </v-list-item>
          <v-list-item>
            <v-list-item-title>Dernière visite</v-list-item-title>
            <v-list-item-subtitle>{{ user.lastVisit || 'Non renseignée' }}</v-list-item-subtitle>
          </v-list-item>
          <v-list-item>
            <v-list-item-title>Statut</v-list-item-title>
            <v-list-item-subtitle>{{ user.status }}</v-list-item-subtitle>
          </v-list-item>
        </v-list>
        <v-divider class="my-4" />
        <v-list dense>
          <v-list-item>
            <v-list-item-title>Forums suivis</v-list-item-title>
            <v-list-item-subtitle>
              <span v-if="user.forums && user.forums.length">
                <v-chip v-for="forum in user.forums" :key="forum.id" class="ma-1">{{ forum.titre || forum.id }}</v-chip>
              </span>
              <span v-else>Aucun forum suivi</span>
            </v-list-item-subtitle>
          </v-list-item>
        </v-list>
        <v-divider class="my-4" />
        <v-btn color="primary" class="mt-2" @click="editProfile">Modifier le profil</v-btn>
      </v-card>
    </v-container>
  </v-app>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import type { Utilisateur } from '@/types/Utilisateur'
import { useRouter } from 'vue-router'

// Exemple de données utilisateur (à remplacer par l'API ou le store)
const user = ref<Utilisateur>({
  id: 1,
  username: 'johndoe',
  roles: ['user'],
  dateCreation: '2024-01-01',
  anonimus: false,
  status: 'active',
  mail: 'johndoe@example.com',
  lastVisit: '2025-07-19',
  forums: [
    { id: 1, titre: 'Forum général', ordreAffichage: 1, visible: true, slug: 'forum-general', dateCreation: null, createdAt: null },
    { id: 2, titre: 'Matériel', ordreAffichage: 2, visible: true, slug: 'materiel', dateCreation: null, createdAt: null },
  ],
})

const router = useRouter()

function editProfile() {
  // Redirige vers une page d'édition du profil (à créer)
  router.push('/profil/edit')
}

function statusColor(status: Utilisateur['status']) {
  switch (status) {
    case 'active': return 'success'
    case 'banned': return 'error'
    case 'pending': return 'warning'
    case 'suspended': return 'orange'
    case 'deleted': return 'grey'
    default: return 'info'
  }
}
</script>
