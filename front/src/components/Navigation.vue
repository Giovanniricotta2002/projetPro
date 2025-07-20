<template>
  <div>
    <template v-if="!isAuthPage">
      <v-app-bar color="primary">
        <v-app-bar-nav-icon variant="text" @click.stop="drawer = !drawer"></v-app-bar-nav-icon>
        <v-toolbar-title>MuscuScope</v-toolbar-title>
        <v-spacer />
        <v-btn v-if="!isLoggedIn" color="white" @click="handleLogin">Connexion</v-btn>
        <v-btn v-else color="white" @click="handleLogout">Déconnexion</v-btn>
      </v-app-bar>

      <v-navigation-drawer v-model="drawer" :location="$vuetify.display.mobile ? 'bottom' : undefined" temporary>
        <v-list>
          <router-link v-for="item in items" :key="item.value" :to="item.value" style="text-decoration: none; color: inherit">
            <v-list-item :value="item.value">
              <v-list-item-title>{{ item.title }}</v-list-item-title>
            </v-list-item>
          </router-link>
        </v-list>
      </v-navigation-drawer>
    </template>

    <v-main style="height: 500px;">
      <v-card-text>
        <slot></slot>
      </v-card-text>
    </v-main>
  </div>
</template>

<script setup lang="ts">
import routes from '@/router/routes';
import { ref, computed, watchEffect } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter, useRoute } from 'vue-router'

const drawer = ref(false)
const items = routes.filter(route => route.meta?.menu).map(route => ({
    title: route.name || 'Untitled',
    value: route.path
}))
const group = ref(null)
const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const isLoggedIn = computed(() => auth.isAuthenticated)
const isAuthPage = computed(() => route.path === '/register' || route.path === '/login')

const handleLogin = () => {
  router.push('/login')
}
const handleLogout = () => {
  auth.stopAuthCheck && auth.stopAuthCheck()
  // Appel de la vraie déconnexion si elle existe
  if (auth.logout) auth.logout()
  router.push('/logout')
}

watchEffect(() => {
    console.log('Drawer state:', drawer.value)
})
</script>