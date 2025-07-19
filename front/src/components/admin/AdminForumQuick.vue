<template>
  <v-list>
    <v-list-item v-for="forum in filteredForums" :key="'forum-quick-'+forum.id">
      <v-list-item-title>{{ forum.titre }}</v-list-item-title>
      <v-btn size="small" color="error" @click="$emit('delete-forum', forum)">Supprimer</v-btn>
    </v-list-item>
  </v-list>
</template>

<script setup lang="ts">
import type { Forum } from '@/types/Forum'

defineProps<{ filteredForums: Forum[] }>()
defineEmits(['delete-forum'])
</script>
