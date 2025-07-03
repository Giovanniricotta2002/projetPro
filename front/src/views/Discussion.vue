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
              <div v-for="(msg, i) in messages" :key="i" class="discussion-msg">
                <div class="msg-header">
                  <span class="msg-author">{{ msg.author }}</span>
                  <span class="msg-date">— {{ msg.date }}</span>
                </div>
                <div class="msg-content">{{ msg.content }}</div>
                <v-divider class="my-2" v-if="i < messages.length - 1" />
              </div>
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
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </v-app>
</template>

<script setup lang="ts">
import { ref } from 'vue'

interface DiscussionMessage {
  author: string
  content: string
  date: string
}

const messages = ref<DiscussionMessage[]>([
  { author: 'MachineBot', content: 'Bienvenue sur la page de discussion des machines. Posez vos questions ici !', date: '2025-07-03 14:30' },
  { author: 'Utilisateur', content: 'Comment entretenir une fraiseuse CNC ?', date: '2025-07-03 14:32' },
  { author: 'ExpertMachine', content: 'Pour l’entretien, pensez à lubrifier les axes et à vérifier les capteurs régulièrement.', date: '2025-07-03 14:35' },
])

const newMessage = ref('')

function postMessage() {
  if (!newMessage.value) return
  messages.value.push({
    author: 'Vous',
    content: newMessage.value,
    date: new Date().toLocaleString('fr-FR', { hour: '2-digit', minute: '2-digit', year: 'numeric', month: '2-digit', day: '2-digit' })
  })
  newMessage.value = ''
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
