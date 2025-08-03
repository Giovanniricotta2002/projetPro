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
            <v-textarea
              v-model="newForumDescription"
              label="Description du forum"
              rows="3"
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
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import type { Forum } from '@/types/Forum'
import type { CategorieForum } from '@/types/CategorieForum'

const forums = ref<Forum[]>([])
const authStore = useAuthStore()

const snackbar = ref<{ show: boolean; color: string; text: string }>({ show: false, color: 'success', text: '' })
const showSnackbar = (msg: string, color: 'error'|'success' = 'error') => {
  return { show: true, color: color, text: msg }
}

onMounted(async () => {
  try {
    const response = await authStore.apiRequest<Forum[]>('/api/forum', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    })
    if (response.success && response.data) {
      forums.value = response.data
    } else {
      snackbar.value = showSnackbar(response.message || 'Erreur lors du chargement des forums')
    }
  } catch (e) {
    snackbar.value = showSnackbar('Erreur lors du chargement des forums')
  }
})

const router = useRouter()
const dialog = ref(false)
const newForumTitle = ref('')
const newForumCategory = ref<number|null>(null)
const search = ref('')
const newForumDescription = ref('')

const categories = ref<CategorieForum[]>([])

onMounted(async () => {
  try {
    const response = await authStore.apiRequest<CategorieForum[]>('/api/categorie-forum', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    })
    if (response.success && response.data) {
      categories.value = response.data
    } else {
      snackbar.value = showSnackbar(response.message || 'Erreur lors du chargement des catégories')
    }
  } catch (e) {
    snackbar.value = showSnackbar('Erreur lors du chargement des catégories')
  }
})

const filteredForums = computed(() => {
  if (!search.value) return forums.value
  return forums.value.filter(f =>
    f.titre && f.titre.toLowerCase().includes(search.value.toLowerCase())
  )
})

async function deleteMessage(index: number) {
  const forum = forums.value[index]
  if (!forum) return
  try {
    const response = await authStore.apiRequest(`/api/forum/${forum.id}`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
      },
    })
    if (response.success) {
      forums.value.splice(index, 1)
      snackbar.value = showSnackbar('Forum supprimé avec succès!', 'success')
    } else {
      snackbar.value = showSnackbar(response.message || 'Erreur lors de la suppression du forum')
    }
  } catch (e) {
    snackbar.value = showSnackbar('Erreur lors de la suppression du forum')
  }
}

function openForum(forum: Forum) {
  router.push({
    path: `/forum/${forum.id}/posts`,
    query: { forumTitre: forum.titre }
  })
}

async function submitNewForum() {
  if (!newForumTitle.value.trim() || !newForumCategory.value) return
  try {
    
    const response = await authStore.apiRequest<Forum>('/api/forum/', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        titre: newForumTitle.value.trim(),
        categories: newForumCategory.value,
        description: newForumDescription.value.trim(),
        ordreAffichage: forums.value.length + 1,
        // utilisateur: authStore.user.value.id,
      })
    })
    if (response.success && response.data) {
      forums.value.unshift(response.data)
      snackbar.value = showSnackbar('Forum créé avec succès!', 'success')
      newForumTitle.value = ''
      newForumCategory.value = null
      dialog.value = false
    } else {
      snackbar.value = showSnackbar(response.message || 'Erreur lors de la création du forum')
    }
  } catch (e) {
    snackbar.value = showSnackbar('Erreur lors de la création du forum')
  }
}
</script>

<style scoped>
.v-list-item {
  border-bottom: 1px solid #eee;
}
</style>
