import Accueil from '../views/Accueil.vue'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import Forum from '../views/Forum.vue'
import Discussion from '../views/Discussion.vue'
import type { RouteConfig } from '@/types/router'
import Machines from '@/views/Machines.vue'
import MaterielInfo from '@/views/MaterielInfo.vue'
import EditMachine from '@/views/EditMachine.vue'
import Posts from '@/views/Posts.vue'
import { roleGuard } from './guards'
import Logout from '@/views/Logout.vue' // Import de la vue Logout
import Admin from '@/views/Admin.vue'
import MachineCreate from '@/views/CreateMachine.vue'
import Utilisateur from '@/views/UserProfile.vue'

const routes: RouteConfig[] = [
  { 
    path: '/', 
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
    path: '/forum/:forumId/posts',
    name: 'posts',
    component: Posts,
    meta: { requiresAuth: false, menu: false }
  },
  {
    path: '/:postId/discussion',
    name: 'discussion',
    component: Discussion,
    meta: { requiresAuth: false, menu: false}
  },
  {
    path: '/materiels',
    name: 'materiels',
    component: Machines,
    meta: { requiresAuth: false, menu: true }
  },
  {
    path: '/materiel/:materielId/edit',
    name: 'materiel_edit',
    component: EditMachine,
    meta: { requiresAuth: false, menu: false },
    beforeEnter: roleGuard('ROLE_ADMIN', 'ROLE_EDITEUR') // Utilisation du roleGuard pour restreindre l'accès
  },
  {
    path: '/materiel/:materielId',
    name: 'materiel',
    component: MaterielInfo,
    meta: { requiresAuth: true, menu: false }
  },
  {
    path: '/logout',
    name: 'logout',
    component: Logout,
    meta: { requiresAuth: true, menu: false }
  },
  {
    path: '/materiel/create',
    name: 'materiel_create',
    component: MachineCreate,
    meta: { requiresAuth: true, menu: false},
    beforeEnter: roleGuard('ROLE_ADMIN', 'ROLE_EDITEUR')
  },
  {
    path: '/profil',
    name: 'Profil',
    component: Utilisateur,
    meta: { requiresAuth: true, menu: true }
  },
  {
    path: '/admin',
    name: 'Admin',
    component: Admin,
    meta: { requiresAuth: true, menu: true, admin: true, allowedRoles: ['ROLE_ADMIN'] },
    beforeEnter: roleGuard('ROLE_ADMIN')
  },
  // Exemple d'utilisation du roleGuard pour une route admin
  // { 
  //   path: '/admin', 
  //   component: AdminDashboard, 
  //   meta: { requiresAuth: true },
  //   beforeEnter: roleGuard('admin')
  // },
]

export default routes
