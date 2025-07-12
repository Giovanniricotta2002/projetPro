<template>
  <v-list>
    <v-list-item v-for="(entry, idx) in history" :key="'history-'+idx">
      <v-list-item-title>{{ entry.date }} : {{ entry.text }}</v-list-item-title>
    </v-list-item>
  </v-list>
</template>

<script setup lang="ts">
defineProps<{ history: { date: string; text: string }[] }>()
</script>
