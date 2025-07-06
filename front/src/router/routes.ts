import Accueil from '../views/Accueil.vue'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import Forum from '../views/Forum.vue'
import Discussion from '../views/Discussion.vue'
import Materiel from '../views/Materiel.vue'
import type { RouteConfig } from '@/types/router'
import Materiels from '@/views/Materiels.vue'
// import { roleGuard } from './guards' // Décommente si besoin

const routes: RouteConfig[] = [
  { 
    path: '', 
    name: 'Accueil',
    component: Accueil, 
    meta: { requiresAuth: true, menu: true } 
  },
  { 
    path: '/login', 
    name: 'login', 
    component: Login, 
    meta: { requiresGuest: false, menu: false }, 
  },
  { 
    path: '/register', 
    name: 'register', 
    component: Register, 
    meta: { requiresGuest: false, menu: false} 
  },
  {
    path: '/forum',
    name: 'forum',
    component: Forum,
    meta: { requiresAuth: false, menu: true }
  },
  {
    path: '/discussion',
    name: 'discussion',
    component: Discussion,
    meta: { requiresAuth: false, menu: true}
  },
  {
    path: '/materiels',
    name: 'materiels',
    component: Materiels,
    meta: { requiresAuth: false, menu: true }
  },
  {
    path: '/materiel/:id',
    name: 'materiel',
    component: Materiel,
    meta: { requiresAuth: false, menu: false }
  }
  // Exemple d'utilisation du roleGuard pour une route admin
  // { 
  //   path: '/admin', 
  //   component: AdminDashboard, 
  //   meta: { requiresAuth: true },
  //   beforeEnter: roleGuard('admin')
  // },
]

export default routes
