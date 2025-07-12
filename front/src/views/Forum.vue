<template>
  <v-app>
    <v-container>
      <v-row class="mb-6">
        <v-col cols="12">
          <v-card class="pa-4">
            <v-card-title class="headline d-flex align-center justify-space-between">
              <span>Forum</span>
              <v-btn color="success" @click="dialog = true">Nouveau forum</v-btn>
            </v-card-title>
            <v-card-text>
              <v-text-field
                v-model="search"
                label="Rechercher un forum par titre"
                prepend-inner-icon="mdi-magnify"
                class="mb-4"
                clearable
              />
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="12">
          <v-list two-line>
            <v-list-item v-for="(forum, i) in filteredForums" :key="forum.id" class="mb-2">
              <v-list-item-avatar color="primary">
                <v-icon>mdi-account</v-icon>
              </v-list-item-avatar>
              <v-list-item-content>
                <v-list-item-title>{{ forum.titre }}</v-list-item-title>
                <v-list-item-subtitle>{{ forum.description }}</v-list-item-subtitle>
                <div v-if="forum.categorieForums && forum.categorieForums.length" class="forum-categories">
                  <v-chip v-for="cat in forum.categorieForums" :key="cat.id" class="ma-1" color="secondary" label>{{ cat.name }}</v-chip>
                </div>
              </v-list-item-content>
              <v-list-item-action>
                <span class="grey--text text--darken-1" style="font-size: 0.8em">{{ forum.dateCreation ? new Date(forum.dateCreation).toLocaleString('fr-FR', { hour: '2-digit', minute: '2-digit', year: 'numeric', month: '2-digit', day: '2-digit' }) : '' }}</span>
                <v-btn icon size="small" color="error" @click="deleteMessage(i)">
                  <v-icon>mdi-delete</v-icon>
                </v-btn>
                <v-btn icon size="small" color="primary" @click="openForum(forum)">
                  <v-icon>mdi-open-in-new</v-icon>
                </v-btn>
              </v-list-item-action>
            </v-list-item>
          </v-list>
        </v-col>
        <v-col cols="12" class="mt-4">
          <v-alert type="info" border="start" color="warning" variant="tonal">
            <span class="font-weight-bold">Règles du forum :</span> Respectez les autres, pas de spam, restez dans le sujet.
          </v-alert>
        </v-col>
      </v-row>

      <v-dialog v-model="dialog" max-width="500">
        <v-card>
          <v-card-title>Créer un nouveau forum</v-card-title>
          <v-card-text>
            <v-text-field v-model="newForumTitle" label="Titre du forum" required autofocus />
            <v-select
              v-model="newForumCategory"
              :items="categories"
              item-title="name"
              item-value="id"
              label="Catégorie du forum"
              required
            />
          </v-card-text>
          <v-card-actions>
            <v-spacer />
            <v-btn text @click="dialog = false">Annuler</v-btn>
            <v-btn color="success" @click="submitNewForum">Créer</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-container>
  </v-app>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import type { Forum } from '@/types/Forum'
import type { CategorieForum } from '@/types/CategorieForum'

const forums = ref<Forum[]>([
  {
    id: 1,
    titre: 'Bienvenue sur le forum !',
    dateCreation: '2025-07-03T14:00:00',
    ordreAffichage: 1,
    visible: true,
    slug: 'bienvenue',
    createdAt: '2025-07-03T14:00:00',
    updatedAt: '2025-07-03T14:00:00',
    deletedAt: null,
    description: 'Forum d’accueil',
    categorieForums: [
      { id: 1, name: 'Accueil', ordre: 1, slug: 'accueil', createdAt: '2025-07-03T14:00:00', updatedAt: '2025-07-03T14:00:00' }
    ]
  },
  {
    id: 2,
    titre: 'Discussions générales',
    dateCreation: '2025-07-03T14:05:00',
    ordreAffichage: 2,
    visible: true,
    slug: 'general',
    createdAt: '2025-07-03T14:05:00',
    updatedAt: '2025-07-03T14:05:00',
    deletedAt: null,
    description: 'Parlez de tout ici',
    categorieForums: [
      { id: 2, name: 'Général', ordre: 1, slug: 'general', createdAt: '2025-07-03T14:05:00', updatedAt: '2025-07-03T14:05:00' },
      { id: 3, name: 'Blabla', ordre: 2, slug: 'blabla', createdAt: '2025-07-03T14:06:00', updatedAt: '2025-07-03T14:06:00' }
    ]
  },
])

const router = useRouter()
const dialog = ref(false)
const newForumTitle = ref('')
const newForumCategory = ref<number|null>(null)
const search = ref('')

const categories = ref<CategorieForum[]>([
  { id: 1, name: 'Accueil', ordre: 1, slug: 'accueil', createdAt: '', updatedAt: '' },
  { id: 2, name: 'Général', ordre: 2, slug: 'general', createdAt: '', updatedAt: '' },
  { id: 3, name: 'Blabla', ordre: 3, slug: 'blabla', createdAt: '', updatedAt: '' },
])

const filteredForums = computed(() => {
  if (!search.value) return forums.value
  return forums.value.filter(f =>
    f.titre && f.titre.toLowerCase().includes(search.value.toLowerCase())
  )
})

function deleteMessage(index: number) {
  forums.value.splice(index, 1)
}

function openForum(forum: Forum) {
  router.push(`/forum/${forum.id}/posts`)
}

function submitNewForum() {
  if (!newForumTitle.value.trim() || !newForumCategory.value) return
  const cat = categories.value.find(c => c.id === newForumCategory.value)
  forums.value.unshift({
    id: Date.now(),
    titre: newForumTitle.value.trim(),
    dateCreation: new Date().toISOString(),
    ordreAffichage: forums.value.length + 1,
    visible: true,
    slug: newForumTitle.value.trim().toLowerCase().replace(/\s+/g, '-'),
    createdAt: new Date().toISOString(),
    updatedAt: null,
    deletedAt: null,
    description: '',
    dateCloture: null,
    post: [],
    categorieForums: cat ? [cat] : [],
    utilisateur: null,
    machine: null,
  })
  newForumTitle.value = ''
  newForumCategory.value = null
  dialog.value = false
}
</script>

<style scoped>
.v-list-item {
  border-bottom: 1px solid #eee;
}
</style>
