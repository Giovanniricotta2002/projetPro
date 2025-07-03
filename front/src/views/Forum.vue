<template>
  <v-app>
    <v-container>
      <v-row class="mb-6">
        <v-col cols="12">
          <v-card class="pa-4">
            <v-card-title class="headline">Forum</v-card-title>
            <v-card-text>
              <v-form @submit.prevent="postMessage">
                <v-text-field
                  v-model="newMessage"
                  label="Votre message"
                  outlined
                  dense
                  :rules="[v => !!v || 'Message requis']"
                />
                <v-btn type="submit" color="primary" :disabled="!newMessage">Poster</v-btn>
              </v-form>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="12">
          <v-list two-line>
            <v-list-item v-for="(msg, i) in messages" :key="i" class="mb-2">
              <v-list-item-avatar color="primary">
                <v-icon>mdi-account</v-icon>
              </v-list-item-avatar>
              <v-list-item-content>
                <v-list-item-title>{{ msg.author }}</v-list-item-title>
                <v-list-item-subtitle>{{ msg.content }}</v-list-item-subtitle>
              </v-list-item-content>
              <v-list-item-action>
                <span class="grey--text text--darken-1" style="font-size: 0.8em">{{ msg.date }}</span>
              </v-list-item-action>
            </v-list-item>
          </v-list>
        </v-col>
      </v-row>
    </v-container>
  </v-app>
</template>

<script setup lang="ts">
import { ref } from 'vue'

interface ForumMessage {
  author: string
  content: string
  date: string
}

const messages = ref<ForumMessage[]>([
  { author: 'Alice', content: 'Bienvenue sur le forum !', date: '2025-07-03 14:00' },
  { author: 'Bob', content: 'Salut à tous !', date: '2025-07-03 14:05' },
])

const newMessage = ref('')

function postMessage() {
  if (!newMessage.value) return
  messages.value.unshift({
    author: 'Vous',
    content: newMessage.value,
    date: new Date().toLocaleString('fr-FR', { hour: '2-digit', minute: '2-digit', year: 'numeric', month: '2-digit', day: '2-digit' })
  })
  newMessage.value = ''
}
</script>

<style scoped>
.v-list-item {
  border-bottom: 1px solid #eee;
}
</style>
