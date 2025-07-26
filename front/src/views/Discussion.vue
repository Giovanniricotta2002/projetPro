<template>
  <v-app>
    <v-container>
      <v-row justify="center">
        <v-col cols="12" md="8">
          <v-card class="pa-4" elevation="2">
            <v-card-title class="headline">Discussion Machines</v-card-title>
            <v-card-text>
              <div class="discussion-intro">
                <v-icon color="primary" class="mr-2">mdi-robot</v-icon>
                <span>Bienvenue sur la page de discussion dédiée aux machines, inspirée du style Wikipédia. Posez vos questions, partagez des idées ou discutez des sujets techniques liés aux machines.</span>
              </div>
              <v-divider class="my-4" />
              <v-form @submit.prevent="postMessage" class="mt-6">
                <v-textarea
                  v-model="newMessage"
                  label="Ajouter un message à la discussion..."
                  auto-grow
                  outlined
                  dense
                  :rules="[v => !!v || 'Message requis']"
                  />
                  <v-btn type="submit" color="primary" :disabled="!newMessage">Publier</v-btn>
              </v-form>
                <v-divider class="my-4" />
              <div v-for="(msg, i) in messages" :key="msg.id" class="discussion-msg">
                <div class="msg-header">
                  <span class="msg-author">{{ msg.utilisateur.anonimus ? 'Anonyme' : msg.utilisateur.username }}</span>
                  <span class="msg-date">— {{ msg.dateCreation ? new Date(msg.dateCreation).toLocaleString('fr-FR', { hour: '2-digit', minute: '2-digit', year: 'numeric', month: '2-digit', day: '2-digit' }) : '' }}</span>
                  <v-btn v-if="canModerate()" icon size="small" color="error" class="ml-2" @click="deleteMessage(i)">
                    <v-icon>mdi-delete</v-icon>
                  </v-btn>
                </div>
                <div class="msg-content" v-html="renderMarkdown(msg.text)"></div>
                <v-divider class="my-2" v-if="i < messages.length - 1" />
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
    <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="4000">
      {{ snackbar.text }}
    </v-snackbar>
  </v-app>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import { marked } from 'marked'
import { useAuthStore } from '@/stores/auth'
import type { Message } from '@/types/Message'


const authStore = useAuthStore()
const route = useRoute()

const currentUser = computed(() => authStore.user)
const userRoles = computed(() => authStore.user?.roles || [])


const messages = ref<Message[]>([])
const snackbar = ref({ show: false, color: 'success', text: '' })
const showSnackbar = (msg: string, color: 'error'|'success' = 'error') => {
  return { show: true, color: color, text: msg }
}

const postId = route.params.postId

async function fetchMessages() {
  try {
    const response = await authStore.apiRequest<Message[]>(`/api/messages/${postId}`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' },
    })
    if (response.success && response.data) {
      messages.value = response.data
    } else {
      snackbar.value = showSnackbar(response.message || 'Erreur lors du chargement des messages')
      messages.value = []
    }
  } catch (e) {
    snackbar.value = showSnackbar('Erreur lors du chargement des messages')
    messages.value = []
  }
}

onMounted(fetchMessages)

const newMessage = ref('')

async function postMessage() {
  if (!newMessage.value || !currentUser.value) return
  try {
    const response = await authStore.apiRequest<Message>(`/api/messages/${postId}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        text: newMessage.value,
        utilisateurId: currentUser.value.id,
      })
    })
    if (response.success && response.data) {
      snackbar.value = showSnackbar('Message publié avec succès !', 'success')
      // Ajout local du message retourné par l'API
      messages.value.push(response.data)
      newMessage.value = ''
    } else {
      snackbar.value = showSnackbar(response.message || 'Erreur lors de la publication du message')
    }
  } catch (e) {
    snackbar.value = showSnackbar('Erreur lors de la publication du message')
  }
}

function renderMarkdown(text: string | null) {
  return marked.parse(text || '')
}

function canModerate() {
  // return hasRole('admin') || hasRole('editor')
  return userRoles.value.includes('admin') || userRoles.value.includes('editor')
}

async function deleteMessage(index: number) {
  const message = messages.value[index]
  if (!message) return
  try {
    const response = await authStore.apiRequest(`/messages/${message.id}`, {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
    })
    if (response.success) {
      messages.value.splice(index, 1)
      snackbar.value = showSnackbar('Message supprimé avec succès !', 'success')
    } else {
      snackbar.value = showSnackbar(response.message || 'Erreur lors de la suppression du message')
    }
  } catch (e) {
    snackbar.value = showSnackbar('Erreur lors de la suppression du message')
  }
}
</script>

<style scoped>
.discussion-intro {
  display: flex;
  align-items: center;
  font-size: 1.1em;
  margin-bottom: 1em;
}
.discussion-msg {
  margin-bottom: 0.5em;
}
.msg-header {
  font-weight: bold;
  color: #2d3a4a;
  margin-bottom: 0.2em;
}
.msg-author {
  color: #1976d2;
}
.msg-date {
  color: #888;
  font-size: 0.9em;
  margin-left: 0.5em;
}
.msg-content {
  margin-left: 1em;
  margin-bottom: 0.5em;
}
</style>
