<template>
  <v-container class="py-8">
    <v-text-field v-model="search" label="Rechercher (utilisateur, forum, post, discussion)" class="mb-6" clearable prepend-inner-icon="mdi-magnify" />
    <v-card elevation="3" class="mx-auto" max-width="1100">
      <v-card-title class="headline text-center">Administration</v-card-title>
      <v-card-text>
        <v-row>
          <v-col cols="12">
            <h3>Forums, posts et discussions</h3>
            <AdminForums
              :filtered-forums="filteredForums"
              :filtered-posts="filteredPosts"
              :filtered-discussions="filteredDiscussions"
              :orphan-discussions="orphanDiscussions"
              :forum-select-items="forumSelectItems"
              :post-select-items="postSelectItems"
              @delete-post="deletePost"
              @delete-discussion="deleteDiscussion"
              @select-discussion="selectDiscussion"
            />
          </v-col>
          <v-col cols="12" md="6">
            <h3>Utilisateurs</h3>
            <AdminUsers :filtered-users="filteredUsers" @ban-user="banUser" />
          </v-col>
          <v-col cols="12" md="6">
            <h3>Forums (gestion rapide)</h3>
            <AdminForumQuick :filtered-forums="filteredForums" @delete-forum="deleteForum" />
          </v-col>
        </v-row>
        <v-divider class="my-6" />
      </v-card-text>
      <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="4000">
        {{ snackbar.text }}
      </v-snackbar>
    </v-card>
  </v-container>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import AdminForums from '@/components/admin/AdminForums.vue'
import AdminUsers from '@/components/admin/AdminUsers.vue'
import AdminForumQuick from '@/components/admin/AdminForumQuick.vue'
import AdminDiscussionHistory from '@/components/admin/AdminDiscussionHistory.vue'
import { useAuth } from '@/composables/useAuth'
import type { Forum } from '@/types/Forum'
import type { Post } from '@/types/Post'
import type { Utilisateur } from '@/types/Utilisateur'
import type { Message } from '@/types/Message'
import { useAuthStore } from '@/stores/auth'
import { onMounted } from 'vue'

const search = ref('')
const users = ref<Utilisateur[]>([])

const authStore = useAuthStore()
const snackbar = ref({ show: false, color: 'success', text: '' })

const showSnackbar = (msg: string, color: 'error'|'success' = 'error') => {
  return { show: true, color: color, text: msg }
}

onMounted(async () => {
  try {
    const result = await authStore.apiRequest<Utilisateur[]>('/api/utilisateur/all', {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' },
    })

    if (result.success && result.data) {
      users.value = result.data
    } else {
      snackbar.value = showSnackbar(result.message || 'Erreur lors du chargement des utilisateurs')
      users.value = []
    }
    
  } catch (e) {
    snackbar.value = showSnackbar('Erreur lors du chargement des utilisateurs')
    users.value = []
  }
})

const forums = ref<Forum[]>([])


onMounted(async () => {
  try {
    const result = await authStore.apiRequest<Forum[]>('/api/forum', {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' }
    })
    if (result.success && Array.isArray(result.data)) {
      forums.value = result.data
    } else {
      snackbar.value = showSnackbar(result.message || 'Erreur lors du chargement des forums')
      forums.value = []
    }
  } catch (e) {
    snackbar.value = showSnackbar('Erreur lors du chargement des forums')
    forums.value = []
  }
})

interface PostAdmin extends Post { forumId: number }
const posts = ref<PostAdmin[]>([])

onMounted(async () => {
  try {
    const result = await authStore.apiRequest<PostAdmin[]>('/api/post/all', {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' }
    })
    if (result.success && Array.isArray(result.data)) {
      posts.value = result.data
    } else {
      snackbar.value = showSnackbar(result.message || 'Erreur lors du chargement des posts')
      posts.value = []
    }
  } catch (e) {
    snackbar.value = showSnackbar('Erreur lors du chargement des posts')
    posts.value = []
  }
})

// Ajout d'une interface locale pour la gestion admin
interface MessageAdmin extends Message {
  postId: number
  titre: string
  history?: { date: string; text: string }[]
}


const discussions = ref<MessageAdmin[]>([])

onMounted(async () => {
  try {
    const result = await authStore.apiRequest<MessageAdmin[]>('/api/messages/all', {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' }
    })
    if (result.success && Array.isArray(result.data)) {
      discussions.value = result.data
    } else {
      snackbar.value = showSnackbar(result.message || 'Erreur lors du chargement des discussions')
      discussions.value = []
    }
  } catch (e) {
    snackbar.value = showSnackbar('Erreur lors du chargement des discussions')
    discussions.value = []
  }
})

const forumSelectItems = computed(() => forums.value.map(f => ({ title: f.titre || '', value: f.id })))
const postSelectItems = computed(() => posts.value.map(p => ({ title: p.titre, value: p.id })))

const selectedDiscussion = ref<MessageAdmin | null>(null)
function selectDiscussion(discussion: MessageAdmin) {
  // On force la référence à l'objet de la liste pour la réactivité
  if (!discussion.id) return
  // Utilise l'objet réactif de la liste si possible
  const found = discussions.value.find(d => d.id === discussion.id)
  if (found) {
    selectedDiscussion.value = found
  } else {
    // Si la discussion n'est pas dans la liste (cas rare), on crée un proxy réactif minimal
    selectedDiscussion.value = { ...discussion, history: discussion.history ?? [] }
  }
}

const filteredUsers = computed(() => {
  if (!search.value) return users.value
  return users.value.filter((u: Utilisateur) => u.username.toLowerCase().includes(search.value.toLowerCase()))
})
const filteredForums = computed(() => {
  if (!search.value) return forums.value
  return forums.value.filter((f: Forum) => f.titre?.toLowerCase().includes(search.value.toLowerCase()))
})
const filteredPosts = computed(() => {
  if (!search.value) return posts.value
  return posts.value.filter((p: PostAdmin) =>
    p.titre.toLowerCase().includes(search.value.toLowerCase())
  )
})
const filteredDiscussions = computed(() => {
  if (!search.value) return discussions.value
  return discussions.value.filter((d: MessageAdmin) =>
    d.titre.toLowerCase().includes(search.value.toLowerCase()) ||
    (posts.value.find((p: PostAdmin) => p.id === d.postId)?.titre.toLowerCase().includes(search.value.toLowerCase()) ?? false)
  )
})
const orphanDiscussions = computed<MessageAdmin[]>(() => {
  // Discussions dont le postId ne correspond à aucun post existant
  return discussions.value.filter((d: MessageAdmin) => !posts.value.some((p: PostAdmin) => p.id === d.postId))
})

async function banUser(user: Utilisateur) {
  try {
    const result = await authStore.apiRequest(`/api/utilisateur/${user.id}/ban`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    })
    if (result.success) {
      users.value = users.value.filter((u: Utilisateur) => u.id !== user.id)
      snackbar.value = showSnackbar('Utilisateur banni', 'success')
    } else {
      snackbar.value = showSnackbar(result.message || 'Erreur lors du bannissement de l\'utilisateur')
    }
  } catch (e) {
    snackbar.value = showSnackbar('Erreur lors du bannissement de l\'utilisateur')
  }
}
async function deleteForum(forum: Forum) {
  try {
    const result = await authStore.apiRequest('/api/forum/' + forum.id, {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' }
    })
    if (result.success) {
      forums.value = forums.value.filter((f: Forum) => f.id !== forum.id)
      posts.value.forEach((post: PostAdmin) => {
        if (post.forumId === forum.id) post.forumId = -1 // -1 = sans forum
      })
      snackbar.value = showSnackbar('Forum supprimé', 'success')
    } else {
      snackbar.value = showSnackbar(result.message || 'Erreur lors de la suppression du forum')
    }
  } catch (e) {
    snackbar.value = showSnackbar('Erreur lors de la suppression du forum')
  }
}
async function deletePost(post: PostAdmin) {
  try {
    const result = await authStore.apiRequest('/api/post/' + post.id, {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' }
    })
    if (result.success) {
      posts.value = posts.value.filter((p: PostAdmin) => p.id !== post.id)
      discussions.value.forEach((d: MessageAdmin) => {
        if (d.postId === post.id) d.postId = -1 // -1 = sans post
      })
      snackbar.value = showSnackbar('Post supprimé', 'success')
    } else {
      snackbar.value = showSnackbar(result.message || 'Erreur lors de la suppression du post')
    }
  } catch (e) {
    snackbar.value = showSnackbar('Erreur lors de la suppression du post')
  }
}

async function deleteDiscussion(discussion: MessageAdmin) {
  try {
    const result = await authStore.apiRequest('/api/discussion/' + discussion.id, {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' }
    })
    if (result.success) {
      discussions.value = discussions.value.filter((d: MessageAdmin) => d.id !== discussion.id)
      snackbar.value = showSnackbar('Discussion supprimée', 'success')
    } else {
      snackbar.value = showSnackbar(result.message || 'Erreur lors de la suppression de la discussion')
    }
  } catch (e) {
    snackbar.value = showSnackbar('Erreur lors de la suppression de la discussion')
  }
}
</script>
