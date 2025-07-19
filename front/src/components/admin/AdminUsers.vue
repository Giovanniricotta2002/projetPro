<template>
  <v-list>
    <v-list-item v-for="user in filteredUsers" :key="user.id">
      <v-list-item-title>{{ user.username }}</v-list-item-title>
      <v-list-item-subtitle>{{ user.roles[0] }}</v-list-item-subtitle>
      <v-btn size="small" color="error" @click="$emit('ban-user', user)">Bannir</v-btn>
    </v-list-item>
  </v-list>
</template>

<script setup lang="ts">
import type { Utilisateur } from '@/types/Utilisateur'

defineProps<{ filteredUsers: Utilisateur[] }>()
defineEmits(['ban-user'])
</script>
