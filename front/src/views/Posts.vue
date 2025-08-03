<template>
  <v-app>
    <v-container>
      <v-row class="mb-6">
        <v-col cols="12">
          <v-card class="pa-4">
            <v-card-title class="headline d-flex align-center justify-space-between">
              <span>Posts du forum : {{ route.query.forumTitre }}</span>
              <v-btn color="success" @click="createPost" v-if="isAuthenticated || true">Nouveau post</v-btn>
            </v-card-title>
            <v-card-text>
              <v-text-field
                v-model="search"
                label="Rechercher un post par titre"
                prepend-inner-icon="mdi-magnify"
                class="mb-4"
                clearable
              />
              <v-btn color="primary" class="mb-4" @click="goBack">Retour au forum</v-btn>
              <v-row>
                <v-col cols="12" v-if="pinnedPost">
                  <v-fade-transition>
                    <v-card v-if="pinnedPost" elevation="8" class="mb-6">
                      <v-list-item :key="pinnedPost.id">
                        <v-list-item-content>
                          <v-list-item-title>{{ pinnedPost.titre }}</v-list-item-title>
                          <v-list-item-subtitle>
                            {{ pinnedPost.dateCreation ? new Date(pinnedPost.dateCreation).toLocaleString('fr-FR', { hour: '2-digit', minute: '2-digit', year: 'numeric', month: '2-digit', day: '2-digit' }) : '' }}
                            <v-chip class="ml-2" color="primary" size="small" label>Épinglé</v-chip>
                            <v-chip v-if="pinnedPost.verrouille" class="ml-2" color="error" size="small" label>Verrouillé</v-chip>
                          </v-list-item-subtitle>
                        </v-list-item-content>
                        <v-list-item-action v-if="canModerate">
                          <v-btn icon color="primary" @click="openDiscussion(pinnedPost)">
                            <v-icon>mdi-forum</v-icon>
                          </v-btn>
                          <v-btn
                            icon
                            color="warning"
                            @click="togglePin(pinnedPost)"
                            :title="'Désépingler'"
                          >
                            <v-icon>mdi-pin-off</v-icon>
                          </v-btn>
                          <v-btn icon color="error" v-if="canDelete(pinnedPost)" @click="deletePost(pinnedPost)">
                            <v-icon>mdi-delete</v-icon>
                          </v-btn>
                        </v-list-item-action>
                      </v-list-item>
                    </v-card>
                  </v-fade-transition>
                </v-col>
                <v-col cols="12">
                  <v-list two-line>
                    <v-fade-transition group>
                      <div v-for="post in unpinnedPosts" :key="post.id">
                        <v-list-item class="mb-2">
                          <v-list-item-content>
                            <v-list-item-title>{{ post.titre }}</v-list-item-title>
                            <v-list-item-subtitle>
                              {{ post.dateCreation ? new Date(post.dateCreation).toLocaleString('fr-FR', { hour: '2-digit', minute: '2-digit', year: 'numeric', month: '2-digit', day: '2-digit' }) : '' }}
                              <v-chip v-if="post.verrouille" class="ml-2" color="error" size="small" label>Verrouillé</v-chip>
                            </v-list-item-subtitle>
                          </v-list-item-content>
                          <v-list-item-action>
                            <v-btn icon color="primary" @click="openDiscussion(post)">
                              <v-icon>mdi-forum</v-icon>
                            </v-btn>
                          </v-list-item-action>
                          <v-list-item-action v-if="canModerate">
                            <v-btn
                              icon
                              color="warning"
                              :disabled="hasPinnedPost"
                              @click="togglePin(post)"
                              :title="'Épingler'"
                            >
                              <v-icon>mdi-pin</v-icon>
                            </v-btn>
                            <v-btn icon color="error" v-if="canDelete(post)" @click="deletePost(post)">
                              <v-icon>mdi-delete</v-icon>
                            </v-btn>
                          </v-list-item-action>
                        </v-list-item>
                      </div>
                    </v-fade-transition>
                  </v-list>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <v-dialog v-model="dialog" max-width="500">
        <v-card>
          <v-card-title>Créer un nouveau post</v-card-title>
          <v-card-text>
            <v-text-field v-model="newPostTitle" label="Titre du post" autofocus required />
          </v-card-text>
          <v-card-actions>
            <v-spacer />
            <v-btn text @click="dialog = false">Annuler</v-btn>
            <v-btn color="success" @click="submitNewPost">Créer</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-container>
  </v-app>
</template>

<script setup lang="ts">

import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import type { Post } from '@/types/Post'
import { useAuth } from '@/composables/useAuth'


const route = useRoute()
const router = useRouter()
const { user, isAuthenticated } = useAuth()
const authStore = useAuthStore()
const snackbar = ref<{ show: boolean; color: string; text: string }>({ show: false, color: 'success', text: '' })
const showSnackbar = (msg: string, color: 'error'|'success' = 'error') => {
  return { show: true, color: color, text: msg }
}


const forumId = computed(() => Number(route.params.forumId))
const posts = ref<Post[]>([])

onMounted(async () => {
  // GET posts
  try {
    const postsRes = await authStore.apiRequest<Post[]>(`/api/post/${forumId.value}/posts`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' },
    })
    if (postsRes.success && postsRes.data) {
      posts.value = postsRes.data
    } else {
      snackbar.value = showSnackbar(postsRes.message || 'Erreur lors du chargement des posts')
    }
  } catch (e) {
    snackbar.value = showSnackbar('Erreur lors du chargement des posts')
  }
})

const search = ref('')
const filteredPosts = computed(() => {
  if (!search.value) return posts.value
  return posts.value.filter(post =>
    post.titre.toLowerCase().includes(search.value.toLowerCase())
  )
})
const hasPinnedPost = computed(() => posts.value.some(p => p.epingle))
const pinnedPost = computed(() => posts.value.find(p => p.epingle))
const unpinnedPosts = computed(() => filteredPosts.value.filter(p => !p.epingle))
const canModerate = computed(() => {
  if (!user.value) return false
  return (
    user.value.roles?.includes('ROLE_ADMIN') ||
    user.value.roles?.includes('ROLE_EDITOR')
  )
})

function goBack() {
  router.push('/forum')
}

function openDiscussion(post: Post) {
  // Redirige vers la page de discussion du post
  router.push({ name: 'discussion', params: { postId: post.id } })
}

function canDelete(post: Post) {
  if (!user.value) return false
  // Admin ou créateur du post (simulé ici, à adapter avec le vrai champ userId du post)
  return user.value.roles?.includes('ROLE_ADMIN') //|| post.creatorId === user.value.id
}

async function deletePost(post: Post) {
  try {
    const response = await authStore.apiRequest(`/api/post/${post.id}`, {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ utilisateur: user.value?.id })
    })
    if (response.success) {
      posts.value = posts.value.filter(p => p.id !== post.id)
      snackbar.value = showSnackbar('Post supprimé avec succès!', 'success')
    } else {
      snackbar.value = showSnackbar(response.message || 'Erreur lors de la suppression du post')
    }
  } catch (e) {
    snackbar.value = showSnackbar('Erreur lors de la suppression du post')
  }
}

const dialog = ref(false)
const newPostTitle = ref('')

function createPost() {
  dialog.value = true
}

async function submitNewPost() {
  if (!user.value || !newPostTitle.value.trim()) return
  try {
    const response = await authStore.apiRequest<Post>(`/api/post/`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        titre: newPostTitle.value.trim(),
        forum: forumId.value,
      })
    })
    if (response.success && response.data) {
      posts.value.unshift(response.data)
      snackbar.value = showSnackbar('Post créé avec succès!', 'success')
      newPostTitle.value = ''
      dialog.value = false
    } else {
      snackbar.value = showSnackbar(response.message || 'Erreur lors de la création du post')
    }
  } catch (e) {
    snackbar.value = showSnackbar('Erreur lors de la création du post')
  }
}

async function togglePin(post: Post) {
  if (!canModerate.value) return
  try {
    const response = await authStore.apiRequest<Post>(`/api/post/${post.id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        epingle: !post.epingle,
        utilisateur: user.value?.id,
      })
    })
    if (response.success && response.data) {
      post.epingle = response.data.epingle
      snackbar.value = showSnackbar('Post mis à jour!', 'success')
    } else {
      snackbar.value = showSnackbar(response.message || 'Erreur lors de la mise à jour du post')
    }
  } catch (e) {
    snackbar.value = showSnackbar('Erreur lors de la mise à jour du post')
  }
}
</script>

<style scoped>
</style>
