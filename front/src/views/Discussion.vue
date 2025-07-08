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
  </v-app>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { marked } from 'marked'
import type { Message } from '@/types/Message'
import type { Utilisateur } from '@/types/Utilisateur'
import { useAuth } from '@/composables/useAuth'

// const { user, hasRole } = useAuth()
const userRoles = ref<string[]>(['admin']) // ou ['editor'], ou []
// Simule l'utilisateur connecté (à remplacer par le vrai store auth)
const currentUser = ref<Utilisateur>({
  id: 99,
  username: 'AdminDemo',
  roles: ['admin'],
  dateCreation: new Date().toISOString(),
  anonimus: false,
  status: 'active',
})



const messages = ref<Message[]>([
  {
    id: 1,
    text: 'Bienvenue sur la page de discussion des machines. Posez vos questions ici !',
    dateCreation: '2025-07-03T14:30:00',
    visible: true,
    utilisateur: {
      id: 1,
      username: 'MachineBot',
      roles: [],
      dateCreation: '2025-07-03T14:00:00',
      anonimus: false,
      status: 'active',
    },
  },
  {
    id: 2,
    text: 'Comment entretenir une fraiseuse CNC ?',
    dateCreation: '2025-07-03T14:32:00',
    visible: true,
    utilisateur: {
      id: 2,
      username: 'Utilisateur',
      roles: [],
      dateCreation: '2025-07-03T14:00:00',
      anonimus: false,
      status: 'active',
    },
  },
  {
    id: 3,
    text: 'Pour l’entretien, pensez à lubrifier les axes et à vérifier les capteurs régulièrement.',
    dateCreation: '2025-07-03T14:35:00',
    visible: true,
    utilisateur: {
      id: 3,
      username: 'ExpertMachine',
      roles: [],
      dateCreation: '2025-07-03T14:00:00',
      anonimus: false,
      status: 'active',
    },
  },
])

const newMessage = ref('')

function postMessage() {
  if (!newMessage.value) return
  // if (!newMessage.value || !user.value) return
  messages.value.push({
    id: messages.value.length + 1,
    text: newMessage.value,
    dateCreation: new Date().toISOString(),
    visible: true,
    utilisateur: {
      // id: user.value.id,
      // username: user.value.username,
      // roles: user.value.roles,
      // dateCreation: user.value.createdAt || new Date().toISOString(),
      // anonimus: user.value.anonimus ?? false,
      // status: user.value.status || 'active',
      id: currentUser.value.id,
      username: currentUser.value.username,
      roles: currentUser.value.roles,
      dateCreation: currentUser.value.createdAt || new Date().toISOString(),
      anonimus: currentUser.value.anonimus ?? false,
      status: currentUser.value.status || 'active',
    },
  })
  newMessage.value = ''
}

function renderMarkdown(text: string | null) {
  return marked.parse(text || '')
}

function canModerate() {
  // return hasRole('admin') || hasRole('editor')
  return userRoles.value.includes('admin') || userRoles.value.includes('editor')
}

function deleteMessage(index: number) {
  messages.value.splice(index, 1)
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
