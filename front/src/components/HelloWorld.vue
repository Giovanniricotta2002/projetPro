<template>
  <v-app>
    <v-btn @click="logout">logout</v-btn>
  </v-app>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'

  console.log("")
const auth = useAuthStore()

const logout = async () => {
  auth.stopAuthCheck()
  console.log("Logged out")
}

</script>
