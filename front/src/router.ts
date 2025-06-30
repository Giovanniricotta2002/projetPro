// router/index.ts

import { setupLayouts } from 'virtual:generated-layouts'
import { createRouter, createWebHistory } from 'vue-router/auto'
import { authGuard, roleGuard } from './router/guards'
import HelloWorld from './components/HelloWorld.vue'
import Login from './views/Login.vue'
import Register from './views/Register.vue'

const routes = [
  { 
    path: '/', 
    name: 'dashboard',
    component: HelloWorld, 
    meta: { requiresAuth: true, title: 'Tableau de bord' } 
  },
  { 
    path: '/login', 
    name: 'login', 
    component: Login, 
    meta: { requiresGuest: true, title: 'Connexion' } 
  },
  { 
    path: '/register', 
    name: 'register', 
    component: Register, 
    meta: { requiresGuest: true, title: 'Inscription' } 
  },
  
  // Gestion des Machines (selon le schéma DB)
  {
    path: '/machines',
    name: 'machines',
    component: () => import('./views/Machines.vue'),
    meta: { requiresAuth: true, title: 'Machines' }
  },
  {
    path: '/machines/:id',
    name: 'machine-details',
    component: () => import('./views/MachineDetails.vue'),
    meta: { requiresAuth: true, title: 'Détails Machine' }
  },
  {
    path: '/machines/:id/info',
    name: 'machine-info',
    component: () => import('./views/MachineInfo.vue'),
    meta: { requiresAuth: true, title: 'Informations Machine' }
  },
  
  // Forum (CategoriesForum + Forum + Post + Message)
  {
    path: '/forum',
    name: 'forum-categories',
    component: () => import('./views/ForumCategories.vue'),
    meta: { requiresAuth: true, title: 'Catégories Forum' }
  },
  {
    path: '/forum/category/:categoryId',
    name: 'forum-list',
    component: () => import('./views/ForumList.vue'),
    meta: { requiresAuth: true, title: 'Forums' }
  },
  {
    path: '/forum/:forumId',
    name: 'forum-posts',
    component: () => import('./views/ForumPosts.vue'),
    meta: { requiresAuth: true, title: 'Posts' }
  },
  {
    path: '/forum/:forumId/post/:postId',
    name: 'post-messages',
    component: () => import('./views/PostMessages.vue'),
    meta: { requiresAuth: true, title: 'Messages' }
  },
  
  // Profil utilisateur
  {
    path: '/profile',
    name: 'profile',
    component: () => import('./views/Profile.vue'),
    meta: { requiresAuth: true, title: 'Mon profil' }
  },
  {
    path: '/profile/settings',
    name: 'profile-settings',
    component: () => import('./views/ProfileSettings.vue'),
    meta: { requiresAuth: true, title: 'Paramètres' }
  },
  {
    path: '/users/:id',
    name: 'user-profile',
    component: () => import('./views/UserProfile.vue'),
    meta: { requiresAuth: true, title: 'Profil utilisateur' }
  },
  
  // Statistiques et Logs
  {
    path: '/stats',
    name: 'statistics',
    component: () => import('./views/Statistics.vue'),
    meta: { requiresAuth: true, title: 'Statistiques' }
  },
  {
    path: '/stats/login-logs',
    name: 'login-logs',
    component: () => import('./views/LoginLogs.vue'),
    meta: { requiresAuth: true, title: 'Logs de connexion' }
  },
  {
    path: '/grafana',
    name: 'grafana',
    component: () => import('./views/GrafanaEmbed.vue'),
    meta: { requiresAuth: true, title: 'Monitoring' }
  },
  
  // Gestion des droits (selon entité Droit)
  {
    path: '/permissions',
    name: 'permissions',
    component: () => import('./views/Permissions.vue'),
    meta: { requiresAuth: true, title: 'Droits d\'accès' },
    beforeEnter: roleGuard('admin')
  },
  
  // Modération (selon entité Moderations)
  {
    path: '/moderation',
    name: 'moderation',
    component: () => import('./views/Moderation.vue'),
    meta: { requiresAuth: true, title: 'Modération' },
    beforeEnter: roleGuard('moderator')
  },
  {
    path: '/moderation/reports',
    name: 'moderation-reports',
    component: () => import('./views/ModerationReports.vue'),
    meta: { requiresAuth: true, title: 'Signalements' },
    beforeEnter: roleGuard('moderator')
  },
  
  // Pages d'édition (rédacteurs)
  {
    path: '/editor',
    name: 'editor',
    component: () => import('./views/Editor.vue'),
    meta: { requiresAuth: true, title: 'Éditeur' },
    beforeEnter: roleGuard('editor')
  },
  {
    path: '/editor/machines/:id',
    name: 'editor-machine',
    component: () => import('./views/EditorMachine.vue'),
    meta: { requiresAuth: true, title: 'Éditer machine' },
    beforeEnter: roleGuard('editor')
  },
  {
    path: '/editor/info-machines/:id',
    name: 'editor-info-machine',
    component: () => import('./views/EditorInfoMachine.vue'),
    meta: { requiresAuth: true, title: 'Éditer infos machine' },
    beforeEnter: roleGuard('editor')
  },
  
  // Administration
  {
    path: '/admin',
    name: 'admin',
    component: () => import('./views/Admin.vue'),
    meta: { requiresAuth: true, title: 'Administration' },
    beforeEnter: roleGuard('admin')
  },
  {
    path: '/admin/users',
    name: 'admin-users',
    component: () => import('./views/AdminUsers.vue'),
    meta: { requiresAuth: true, title: 'Gestion utilisateurs' },
    beforeEnter: roleGuard('admin')
  },
  {
    path: '/admin/forum-categories',
    name: 'admin-forum-categories',
    component: () => import('./views/AdminForumCategories.vue'),
    meta: { requiresAuth: true, title: 'Gestion catégories' },
    beforeEnter: roleGuard('admin')
  },
  {
    path: '/admin/forums',
    name: 'admin-forums',
    component: () => import('./views/AdminForums.vue'),
    meta: { requiresAuth: true, title: 'Gestion forums' },
    beforeEnter: roleGuard('admin')
  },
  {
    path: '/admin/logs',
    name: 'admin-logs',
    component: () => import('./views/AdminLogs.vue'),
    meta: { requiresAuth: true, title: 'Logs système' },
    beforeEnter: roleGuard('admin')
  },
  
  // Déconnexion
  {
    path: '/logout',
    name: 'logout',
    component: () => import('./views/Logout.vue'),
    meta: { requiresAuth: true, title: 'Déconnexion' }
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: setupLayouts(routes),
})

// Utilisation du guard d'authentification amélioré
router.beforeEach(authGuard)

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
