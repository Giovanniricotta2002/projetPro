// router/index.ts

import { setupLayouts } from 'virtual:generated-layouts'
import { createRouter, createWebHistory } from 'vue-router/auto'
import { authGuard, roleGuard } from './router/guards'
import HelloWorld from './components/HelloWorld.vue'
import Login from './views/Login.vue'
import Register from './views/Register.vue'
import Forum from './views/Forum.vue'
import Discussion from './views/Discussion.vue'
import Materiel from './views/Materiel.vue'

const routes = [
  { 
    path: '/', 
    component: HelloWorld, 
    meta: { requiresAuth: true } 
  },
  { 
    path: '/login', 
    name: 'login', 
    component: Login, 
    meta: { requiresGuest: true } 
  },
  { 
    path: '/register', 
    name: 'register', 
    component: Register, 
    meta: { requiresGuest: true } 
  },
  {
    path: '/forum',
    name: 'forum',
    component: Forum,
    // meta: { requiresAuth: true }
  },
  {
    path: '/discussion',
    name: 'discussion',
    component: Discussion,
    // meta: { requiresAuth: true }
  },
  {
    path: '/materiel',
    name: 'materiel',
    component: Materiel,
    // meta: { requiresAuth: true }
  }
  // Exemple d'utilisation du roleGuard pour une route admin
  // { 
  //   path: '/admin', 
  //   component: AdminDashboard, 
  //   meta: { requiresAuth: true },
  //   beforeEnter: roleGuard('admin')
  // },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: setupLayouts(routes),
})

// Utilisation du guard d'authentification amélioré
// router.beforeEach(authGuard)

// Workaround for https://github.com/vitejs/vite/issues/11804
router.onError((err, to) => {
  if (err?.message?.includes?.('Failed to fetch dynamically imported module')) {
    if (localStorage.getItem('vuetify:dynamic-reload')) {
      console.error('Dynamic import error, reloading page did not fix it', err)
    } else {
      console.log('Reloading page to fix dynamic import error')
      localStorage.setItem('vuetify:dynamic-reload', 'true')
      location.assign(to.fullPath)
    }
  } else {
    console.error(err)
  }
})

router.isReady().then(() => {
  localStorage.removeItem('vuetify:dynamic-reload')
  console.log('Router is ready')
})

export default router
