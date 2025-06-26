// router/index.ts

import { setupLayouts } from 'virtual:generated-layouts'
// import { routes } from 'vue-router/auto-routes'
import { createRouter, createWebHistory } from 'vue-router/auto'
import HelloWorld from './components/HelloWorld.vue'
import Login from './views/Login.vue'
import Register from './views/Register.vue'

const routes = [
  { path: '/', component: HelloWorld },
  { path: '/login', name: 'Login', component: Login, meta: { requiresAuth: false} },
  { path: '/register', name: 'Crée un compte', component: Register, meta: { requiresAuth: false} },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: setupLayouts(routes),
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  console.log(token, to.meta)

  const requiresAuth = to.meta.requiresAuth !== false // true si undefined ou true, false uniquement si false

  if (requiresAuth && !token) {
    // Redirect to login if the route requires authentication and no token is present
    next('/login')
  } else {
    next()
  }
})

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
