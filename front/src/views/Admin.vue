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
        <v-row v-if="selectedDiscussion">
          <v-col cols="12">
            <h3>Historique de la discussion : {{ selectedDiscussion?.titre }}</h3>
            <AdminDiscussionHistory :history="selectedDiscussion?.history ?? []" />
          </v-col>
        </v-row>
      </v-card-text>
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

const search = ref('')

const users = ref<Utilisateur[]>([
  { id: 1, username: 'admin', roles: ['admin'], status: 'active', dateCreation: null, anonimus: false },
  { id: 2, username: 'moderator', roles: ['editor'], status: 'active', dateCreation: null, anonimus: false },
  { id: 3, username: 'user1', roles: ['user'], status: 'active', dateCreation: null, anonimus: false },
])

const forums = ref<Forum[]>([
  { id: 1, titre: 'Forum général', ordreAffichage: 1, visible: true, slug: 'forum-general', dateCreation: null, createdAt: null },
  { id: 2, titre: 'Matériel', ordreAffichage: 2, visible: true, slug: 'materiel', dateCreation: null, createdAt: null },
  { id: 3, titre: 'Discussions libres', ordreAffichage: 3, visible: true, slug: 'discussions-libres', dateCreation: null, createdAt: null },
])

interface PostAdmin extends Post { forumId: number }
const posts = ref<PostAdmin[]>([
  { id: 1, titre: 'Bienvenue', dateCreation: '2024-01-01', vues: 0, forumId: 1 },
  { id: 2, titre: 'Matériel préféré', dateCreation: '2024-01-02', vues: 0, forumId: 2 },
  { id: 3, titre: 'Règlement', dateCreation: '2024-01-03', vues: 0, forumId: 1 },
])

// Ajout d'une interface locale pour la gestion admin
interface MessageAdmin extends Message {
  postId: number
  titre: string
  history?: { date: string; text: string }[]
}

const discussions = ref<MessageAdmin[]>([
  { id: 1, postId: 1, titre: 'Présentation', text: 'Présentation', dateCreation: '2024-01-01', visible: true, utilisateur: users.value[0], history: [ { date: '2024-01-01', text: 'Création' }, { date: '2024-02-01', text: 'Modifié' } ] },
  { id: 2, postId: 2, titre: 'Vos avis', text: 'Vos avis', dateCreation: '2024-01-10', visible: true, utilisateur: users.value[1], history: [ { date: '2024-01-10', text: 'Création' } ] },
  { id: 3, postId: 1, titre: 'Questions', text: 'Questions', dateCreation: '2024-01-15', visible: true, utilisateur: users.value[2], history: [ { date: '2024-01-15', text: 'Création' }, { date: '2024-03-01', text: 'Ajout réponse' } ] },
])

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

function banUser(user: Utilisateur) {
  users.value = users.value.filter((u: Utilisateur) => u.id !== user.id)
}
function deleteForum(forum: Forum) {
  forums.value = forums.value.filter((f: Forum) => f.id !== forum.id)
  posts.value.forEach((post: PostAdmin) => {
    if (post.forumId === forum.id) post.forumId = -1 // -1 = sans forum
  })
}
function deletePost(post: PostAdmin) {
  posts.value = posts.value.filter((p: PostAdmin) => p.id !== post.id)
  discussions.value.forEach((d: MessageAdmin) => {
    if (d.postId === post.id) d.postId = -1 // -1 = sans post
  })
}
function deleteDiscussion(discussion: MessageAdmin) {
  discussions.value = discussions.value.filter((d: MessageAdmin) => d.id !== discussion.id)
}

// Gestion des droits d'accès
const { hasRole } = useAuth()

// Restriction d'accès à la page admin (optionnel, à activer si besoin)
// if (!hasRole('admin')) router.push('/')
</script>
