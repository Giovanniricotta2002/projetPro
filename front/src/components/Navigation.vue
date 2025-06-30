<template>
  <v-navigation-drawer
    v-model="drawer"
    :rail="rail && !isMobile"
    :permanent="!isMobile"
    :location="isMobile ? 'bottom' : 'left'"
    :temporary="isMobile"
    class="custom-navigation"
    color="surface"
    theme="dark"
    :width="280"
    :rail-width="72"
    expand-on-hover
  >
    <!-- Header avec logo -->
    <v-list-item
      class="nav-header elevation-2"
      :class="{ 'px-2': rail && !isMobile }"
      @click="!isMobile && toggleRail()"
    >
      <template #prepend>
        <v-avatar 
          color="primary" 
          size="40"
          class="logo-avatar"
        >
          <v-icon size="24" color="white">mdi-dumbbell</v-icon>
        </v-avatar>
      </template>
      
      <v-list-item-title 
        class="text-h6 font-weight-bold text-primary"
        v-show="!rail || isMobile"
      >
        MuscuScope
      </v-list-item-title>
      
      <template #append>
        <v-btn
          v-if="!isMobile"
          variant="text"
          :icon="rail ? 'mdi-chevron-right' : 'mdi-chevron-left'"
          size="small"
          color="primary"
          @click.stop="toggleRail()"
          class="rail-toggle-btn"
        />
      </template>
    </v-list-item>

    <v-divider class="border-opacity-25" />

    <!-- Menu principal -->
    <v-list 
      density="compact" 
      nav 
      class="py-2 main-nav-list"
      :lines="false"
      color="primary"
    >
      <!-- Tableau de bord -->
      <v-list-item
        to="/"
        prepend-icon="mdi-view-dashboard"
        title="Tableau de bord"
        value="dashboard"
        :active="isCurrentPage('/')"
        class="nav-item"
        rounded="xl"
      >
        <template #append>
          <v-icon 
            v-if="isCurrentPage('/')" 
            size="small" 
            color="primary"
          >
            mdi-check-circle
          </v-icon>
        </template>
      </v-list-item>

      <!-- Section Navigation -->
      <v-list-subheader 
        v-if="!rail || isMobile" 
        class="text-primary font-weight-bold nav-section-header"
      >
        <v-icon size="16" class="me-2">mdi-compass</v-icon>
        NAVIGATION
      </v-list-subheader>

      <!-- Machines -->
      <v-list-group 
        value="machines" 
        :class="{ 'mb-2': !rail || isMobile }"
        fluid
      >
        <template #activator="{ props }">
          <v-list-item
            v-bind="props"
            prepend-icon="mdi-cog"
            title="Machines"
            :active="isMachinesActive"
            class="nav-item group-activator"
            rounded="xl"
          >
            <template #append>
              <v-badge
                v-if="machinesCount > 0"
                :content="machinesCount"
                color="info"
                inline
                class="me-2"
              />
            </template>
          </v-list-item>
        </template>

        <v-list-item
          to="/machines"
          prepend-icon="mdi-format-list-bulleted"
          title="Liste complète"
          value="machines-list"
          class="nav-sub-item"
          rounded="lg"
        />
        
        <v-list-item
          v-if="hasEditAccess"
          to="/editor/machines"
          prepend-icon="mdi-pencil"
          title="Éditer machines"
          value="machines-edit"
          class="nav-sub-item"
          rounded="lg"
        />
      </v-list-group>

      <!-- Forum -->
      <v-list-group 
        value="forum" 
        :class="{ 'mb-2': !rail || isMobile }"
        fluid
      >
        <template #activator="{ props }">
          <v-list-item
            v-bind="props"
            prepend-icon="mdi-forum"
            title="Forum"
            :active="isForumActive"
            class="nav-item group-activator"
            rounded="xl"
          >
            <template #append>
              <v-badge
                v-if="unreadMessages > 0"
                :content="unreadMessages"
                color="warning"
                inline
                class="me-2"
              />
            </template>
          </v-list-item>
        </template>

        <v-list-item
          to="/forum"
          prepend-icon="mdi-folder"
          title="Catégories"
          value="forum-categories"
          class="nav-sub-item"
          rounded="lg"
        />
        
        <v-list-item
          to="/forum/recent"
          prepend-icon="mdi-fire"
          title="Messages récents"
          value="forum-recent"
          class="nav-sub-item"
          rounded="lg"
        >
          <template #append>
            <v-chip 
              v-if="recentPostsCount > 0"
              size="x-small" 
              color="error"
              variant="flat"
            >
              {{ recentPostsCount }}
            </v-chip>
          </template>
        </v-list-item>
      </v-list-group>

      <!-- Section Analyse -->
      <v-list-subheader 
        v-if="!rail || isMobile" 
        class="text-info font-weight-bold nav-section-header mt-4"
      >
        <v-icon size="16" class="me-2">mdi-chart-line</v-icon>
        ANALYSE
      </v-list-subheader>

      <!-- Statistiques -->
      <v-list-group 
        value="stats" 
        :class="{ 'mb-2': !rail || isMobile }"
        fluid
      >
        <template #activator="{ props }">
          <v-list-item
            v-bind="props"
            prepend-icon="mdi-chart-line"
            title="Statistiques"
            :active="isStatsActive"
            class="nav-item group-activator"
            rounded="xl"
          />
        </template>

        <v-list-item
          to="/stats"
          prepend-icon="mdi-chart-bar"
          title="Rapports généraux"
          value="stats-general"
          class="nav-sub-item"
          rounded="lg"
        />
        
        <v-list-item
          to="/stats/login-logs"
          prepend-icon="mdi-key"
          title="Logs connexion"
          value="stats-logs"
          class="nav-sub-item"
          rounded="lg"
        />
        
        <v-list-item
          to="/grafana"
          prepend-icon="mdi-monitor-dashboard"
          title="Monitoring"
          value="stats-monitoring"
          class="nav-sub-item"
          rounded="lg"
        >
          <template #append>
            <v-icon 
              :color="grafanaStatus === 'online' ? 'success' : 'error'"
              size="small"
            >
              {{ grafanaStatus === 'online' ? 'mdi-check-circle' : 'mdi-alert-circle' }}
            </v-icon>
          </template>
        </v-list-item>
      </v-list-group>

      <!-- Section Gestion (rôles spéciaux) -->
      <v-list-subheader 
        v-if="(!rail || isMobile) && hasSpecialRoles" 
        class="text-warning font-weight-bold nav-section-header mt-4"
      >
        <v-icon size="16" class="me-2">mdi-shield-crown</v-icon>
        GESTION
      </v-list-subheader>

      <!-- Modération -->
      <v-list-group 
        v-if="authStore.hasRole('moderator') || authStore.hasRole('admin')" 
        value="moderation"
        :class="{ 'mb-2': !rail || isMobile }"
        fluid
      >
        <template #activator="{ props }">
          <v-list-item
            v-bind="props"
            prepend-icon="mdi-shield-account"
            title="Modération"
            :active="isModerationActive"
            class="nav-item group-activator"
            rounded="xl"
          >
            <template #append>
              <v-badge
                v-if="pendingReports > 0"
                :content="pendingReports"
                color="error"
                inline
                class="me-2"
              />
            </template>
          </v-list-item>
        </template>

        <v-list-item
          to="/moderation"
          prepend-icon="mdi-view-dashboard"
          title="Tableau de bord"
          value="moderation-dashboard"
          class="nav-sub-item"
          rounded="lg"
        />
        
        <v-list-item
          to="/moderation/reports"
          prepend-icon="mdi-flag"
          title="Signalements"
          value="moderation-reports"
          class="nav-sub-item"
          rounded="lg"
        >
          <template #append>
            <v-chip
              v-if="pendingReports > 0"
              size="x-small"
              color="error"
              variant="flat"
            >
              {{ pendingReports }}
            </v-chip>
          </template>
        </v-list-item>
      </v-list-group>

      <!-- Éditeur -->
      <v-list-item
        v-if="authStore.hasRole('editor') || authStore.hasRole('admin')"
        to="/editor"
        prepend-icon="mdi-file-edit"
        title="Éditeur"
        value="editor"
        :active="isEditorActive"
        class="nav-item"
        rounded="xl"
      />

      <!-- Administration -->
      <v-list-group 
        v-if="authStore.hasRole('admin')" 
        value="admin"
        :class="{ 'mb-2': !rail || isMobile }"
        fluid
      >
        <template #activator="{ props }">
          <v-list-item
            v-bind="props"
            prepend-icon="mdi-shield-crown"
            title="Administration"
            :active="isAdminActive"
            class="nav-item group-activator"
            rounded="xl"
          >
            <template #append>
              <v-icon 
                color="warning" 
                size="small"
                class="me-2"
              >
                mdi-crown
              </v-icon>
            </template>
          </v-list-item>
        </template>

        <v-list-item
          to="/admin"
          prepend-icon="mdi-view-dashboard"
          title="Vue d'ensemble"
          value="admin-dashboard"
          class="nav-sub-item"
          rounded="lg"
        />
        
        <v-list-item
          to="/admin/users"
          prepend-icon="mdi-account-group"
          title="Utilisateurs"
          value="admin-users"
          class="nav-sub-item"
          rounded="lg"
        >
          <template #append>
            <v-chip 
              v-if="totalUsers > 0"
              size="x-small" 
              color="info"
              variant="outlined"
            >
              {{ totalUsers }}
            </v-chip>
          </template>
        </v-list-item>
        
        <v-list-item
          to="/permissions"
          prepend-icon="mdi-key-chain"
          title="Droits d'accès"
          value="admin-permissions"
          class="nav-sub-item"
          rounded="lg"
        />
        
        <v-list-item
          to="/admin/forum-categories"
          prepend-icon="mdi-folder-settings"
          title="Catégories forum"
          value="admin-forum-categories"
          class="nav-sub-item"
          rounded="lg"
        />
        
        <v-list-item
          to="/admin/logs"
          prepend-icon="mdi-file-document"
          title="Logs système"
          value="admin-logs"
          class="nav-sub-item"
          rounded="lg"
        />
      </v-list-group>
    </v-list>

    <!-- Footer avec profil utilisateur -->
    <template #append>
      <div class="pa-2">
        <v-menu 
          location="top" 
          :close-on-content-click="false"
          transition="slide-y-reverse-transition"
          :offset="8"
        >
          <template #activator="{ props }">
            <v-list-item
              v-bind="props"
              class="user-profile-item elevation-1"
              :class="{ 'px-2': rail && !isMobile }"
              rounded="xl"
            >
              <template #prepend>
                <v-avatar 
                  color="primary" 
                  size="36"
                  class="user-avatar"
                >
                  <v-img 
                    v-if="authStore.user?.avatar"
                    :src="authStore.user.avatar"
                    :alt="authStore.user?.username"
                  />
                  <v-icon v-else color="white">mdi-account</v-icon>
                </v-avatar>
              </template>
              
              <v-list-item-title 
                v-if="!rail || isMobile" 
                class="font-weight-medium text-truncate"
              >
                {{ authStore.user?.username || 'Utilisateur' }}
              </v-list-item-title>
              
              <v-list-item-subtitle 
                v-if="!rail || isMobile"
                class="text-caption"
              >
                {{ getUserRole() }}
              </v-list-item-subtitle>
              
              <template #append v-if="!rail || isMobile">
                <v-badge
                  v-if="unreadNotifications > 0"
                  :content="unreadNotifications"
                  color="error"
                  offset-x="2"
                  offset-y="2"
                  class="me-2"
                >
                  <v-icon size="small" color="medium-emphasis">
                    mdi-bell
                  </v-icon>
                </v-badge>
                <v-icon 
                  v-else
                  size="small" 
                  color="medium-emphasis"
                >
                  mdi-chevron-up
                </v-icon>
              </template>
            </v-list-item>
          </template>

          <v-card 
            min-width="280" 
            max-width="320"
            class="user-menu-card elevation-8"
            rounded="lg"
          >
            <v-card-text class="pa-4">
              <!-- En-tête du profil -->
              <div class="d-flex align-center mb-3">
                <v-avatar 
                  color="primary" 
                  size="48" 
                  class="me-3 user-avatar-large"
                >
                  <v-img 
                    v-if="authStore.user?.avatar"
                    :src="authStore.user.avatar"
                    :alt="authStore.user?.username"
                  />
                  <v-icon v-else color="white" size="24">mdi-account</v-icon>
                </v-avatar>
                <div class="flex-grow-1">
                  <div class="text-subtitle-1 font-weight-medium text-truncate">
                    {{ authStore.user?.username || 'Utilisateur' }}
                  </div>
                  <div class="text-caption text-medium-emphasis">
                    {{ getUserRole() }}
                  </div>
                  <v-chip 
                    size="x-small" 
                    :color="getRoleColor()"
                    variant="flat"
                    class="mt-1"
                  >
                    {{ getUserBadge() }}
                  </v-chip>
                </div>
              </div>

              <!-- Statistiques rapides -->
              <v-row v-if="userStats" dense class="mb-3">
                <v-col cols="4">
                  <div class="text-center">
                    <div class="text-h6 font-weight-bold text-primary">
                      {{ userStats.posts }}
                    </div>
                    <div class="text-caption">Posts</div>
                  </div>
                </v-col>
                <v-col cols="4">
                  <div class="text-center">
                    <div class="text-h6 font-weight-bold text-success">
                      {{ userStats.likes }}
                    </div>
                    <div class="text-caption">Likes</div>
                  </div>
                </v-col>
                <v-col cols="4">
                  <div class="text-center">
                    <div class="text-h6 font-weight-bold text-info">
                      {{ userStats.rank }}
                    </div>
                    <div class="text-caption">Rang</div>
                  </div>
                </v-col>
              </v-row>

              <v-divider class="mb-3" />

              <!-- Menu actions -->
              <v-list density="compact" class="pa-0" nav>
                <v-list-item
                  to="/profile"
                  prepend-icon="mdi-account"
                  title="Mon profil"
                  class="user-menu-item"
                  rounded="lg"
                />
                <v-list-item
                  to="/profile/settings"
                  prepend-icon="mdi-cog"
                  title="Paramètres"
                  class="user-menu-item"
                  rounded="lg"
                />
                <v-list-item
                  v-if="unreadNotifications > 0"
                  to="/notifications"
                  prepend-icon="mdi-bell"
                  title="Notifications"
                  class="user-menu-item"
                  rounded="lg"
                >
                  <template #append>
                    <v-badge
                      :content="unreadNotifications"
                      color="error"
                      inline
                    />
                  </template>
                </v-list-item>
                
                <v-divider class="my-2" />
                
                <v-list-item
                  to="/logout"
                  prepend-icon="mdi-logout"
                  title="Déconnexion"
                  class="text-error user-menu-item"
                  rounded="lg"
                />
              </v-list>
            </v-card-text>
          </v-card>
        </v-menu>
      </div>
    </template>
  </v-navigation-drawer>

  <!-- Fab pour mobile -->
  <v-fab
    v-if="isMobile && !drawer"
    icon="mdi-menu"
    location="bottom end"
    size="small"
    color="primary"
    @click="drawer = true"
    class="mobile-fab"
  />
</template>
<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useDisplay } from 'vuetify'

const route = useRoute()
const authStore = useAuthStore()
const { mobile } = useDisplay()

// États réactifs
const drawer = ref(true)
const rail = ref(false)

// Données simulées pour l'exemple (à remplacer par de vraies données)
const pendingReports = ref(3)
const machinesCount = ref(45)
const unreadMessages = ref(12)
const recentPostsCount = ref(8)
const grafanaStatus = ref<'online' | 'offline'>('online')
const totalUsers = ref(156)
const unreadNotifications = ref(5)

// Statistiques utilisateur simulées
const userStats = ref({
  posts: 42,
  likes: 128,
  rank: 15
})

// Computed properties
const isMobile = computed(() => mobile.value)

const hasEditAccess = computed(() => 
  authStore.hasRole('editor') || authStore.hasRole('admin')
)

const hasSpecialRoles = computed(() => 
  authStore.hasRole('moderator') || authStore.hasRole('editor') || authStore.hasRole('admin')
)

const isCurrentPage = (path: string) => route.path === path

const isMachinesActive = computed(() => route.path.startsWith('/machines'))
const isForumActive = computed(() => route.path.startsWith('/forum'))
const isStatsActive = computed(() => 
  route.path.startsWith('/stats') || route.path.startsWith('/grafana')
)
const isModerationActive = computed(() => route.path.startsWith('/moderation'))
const isEditorActive = computed(() => route.path.startsWith('/editor'))
const isAdminActive = computed(() => 
  route.path.startsWith('/admin') || route.path.startsWith('/permissions')
)

// Fonctions utilitaires
const getUserRole = () => {
  if (authStore.hasRole('admin')) return 'Administrateur'
  if (authStore.hasRole('moderator')) return 'Modérateur'
  if (authStore.hasRole('editor')) return 'Éditeur'
  return 'Utilisateur'
}

const getUserBadge = () => {
  if (authStore.hasRole('admin')) return 'ADMIN'
  if (authStore.hasRole('moderator')) return 'MOD'
  if (authStore.hasRole('editor')) return 'EDIT'
  return 'USER'
}

const getRoleColor = () => {
  if (authStore.hasRole('admin')) return 'error'
  if (authStore.hasRole('moderator')) return 'warning'
  if (authStore.hasRole('editor')) return 'info'
  return 'success'
}

const toggleRail = () => {
  rail.value = !rail.value
}

// Gestion responsive
const handleResize = () => {
  if (mobile.value) {
    rail.value = false
    drawer.value = false
  } else {
    drawer.value = true
  }
}

// Lifecycle
onMounted(() => {
  handleResize()
  window.addEventListener('resize', handleResize)
  
  // Simuler la récupération des données
  setTimeout(() => {
    grafanaStatus.value = Math.random() > 0.3 ? 'online' : 'offline'
  }, 2000)
  
  // Animation d'entrée
  setTimeout(() => {
    drawer.value = true
  }, 100)
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', handleResize)
})
</script>

<style scoped>
/* Variables CSS personnalisées */
.custom-navigation {
  border-right: 1px solid rgba(var(--v-theme-on-surface), 0.12) !important;
  background: linear-gradient(180deg, 
    rgba(var(--v-theme-surface), 1) 0%, 
    rgba(var(--v-theme-surface), 0.98) 100%
  ) !important;
  backdrop-filter: blur(10px);
}

/* Header */
.nav-header {
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  margin: 8px;
  background: rgba(var(--v-theme-primary), 0.05);
  border: 1px solid rgba(var(--v-theme-primary), 0.1);
}

.nav-header:hover {
  background: rgba(var(--v-theme-primary), 0.1);
  transform: translateY(-1px);
}

.logo-avatar {
  transition: transform 0.3s ease;
}

.nav-header:hover .logo-avatar {
  transform: scale(1.05);
}

.rail-toggle-btn {
  transition: transform 0.3s ease;
}

/* Navigation principale */
.main-nav-list {
  padding: 16px 8px !important;
}

.nav-section-header {
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.8px;
  opacity: 0.9;
  margin: 16px 16px 8px 16px;
  display: flex;
  align-items: center;
}

/* Items de navigation */
.nav-item {
  margin: 2px 8px;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.nav-item::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(90deg, 
    rgba(var(--v-theme-primary), 0.1) 0%, 
    rgba(var(--v-theme-primary), 0.05) 100%
  );
  opacity: 0;
  transition: opacity 0.3s ease;
  z-index: -1;
}

.nav-item:hover::before {
  opacity: 1;
}

.nav-item:hover {
  transform: translateX(4px);
  box-shadow: 0 4px 12px rgba(var(--v-theme-primary), 0.15);
}

.group-activator {
  font-weight: 500;
}

.nav-sub-item {
  margin: 2px 8px 2px 24px;
  font-size: 0.875rem;
  opacity: 0.9;
}

.nav-sub-item:hover {
  transform: translateX(6px);
  opacity: 1;
}

/* Profil utilisateur */
.user-profile-item {
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  margin: 8px;
  background: rgba(var(--v-theme-primary), 0.05);
  border: 1px solid rgba(var(--v-theme-primary), 0.1);
}

.user-profile-item:hover {
  background: rgba(var(--v-theme-primary), 0.1);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
}

.user-avatar {
  transition: all 0.3s ease;
  border: 2px solid rgba(var(--v-theme-primary), 0.2);
}

.user-avatar-large {
  border: 3px solid rgba(var(--v-theme-primary), 0.3);
  box-shadow: 0 4px 12px rgba(var(--v-theme-primary), 0.2);
}

.user-menu-card {
  background: rgba(var(--v-theme-surface), 0.98) !important;
  backdrop-filter: blur(20px);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.08);
}

.user-menu-item {
  margin: 2px 0;
  transition: all 0.2s ease;
}

.user-menu-item:hover {
  background: rgba(var(--v-theme-primary), 0.08);
  transform: translateX(4px);
}

/* Badges et notifications */
:deep(.v-badge) {
  --v-badge-size: 18px;
}

:deep(.v-badge .v-badge__badge) {
  font-size: 0.75rem;
  font-weight: 600;
  min-width: 18px;
  height: 18px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

/* Chips améliorés */
:deep(.v-chip) {
  font-weight: 500;
  letter-spacing: 0.025em;
}

/* Items actifs */
:deep(.v-list-item--active) {
  background: linear-gradient(90deg, 
    rgba(var(--v-theme-primary), 0.15) 0%, 
    rgba(var(--v-theme-primary), 0.08) 100%
  ) !important;
  border-left: 4px solid rgb(var(--v-theme-primary)) !important;
  font-weight: 600;
}

:deep(.v-list-item--active .v-list-item__overlay) {
  opacity: 0.1 !important;
}

:deep(.v-list-item--active .v-icon) {
  color: rgb(var(--v-theme-primary)) !important;
}

/* Groupes de navigation */
:deep(.v-list-group__items) {
  background: rgba(var(--v-theme-surface), 0.5);
  border-radius: 8px;
  margin: 4px 0;
  padding: 4px 0;
}

:deep(.v-list-group__items .v-list-item) {
  padding-left: 48px !important;
}

/* Mode rail amélioré */
:deep(.v-navigation-drawer--rail) {
  transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

:deep(.v-navigation-drawer--rail:hover) {
  width: 280px !important;
}

/* FAB mobile */
.mobile-fab {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 1000;
  box-shadow: 0 8px 24px rgba(var(--v-theme-primary), 0.3);
}

/* Animations */
.slide-y-reverse-transition-enter-active,
.slide-y-reverse-transition-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-y-reverse-transition-enter-from {
  opacity: 0;
  transform: translateY(20px);
}

.slide-y-reverse-transition-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

/* Dividers stylisés */
:deep(.v-divider) {
  border-color: rgba(var(--v-theme-on-surface), 0.08);
}

.border-opacity-25 {
  border-color: rgba(var(--v-theme-on-surface), 0.25) !important;
}

/* Text utilities */
.text-truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Responsive adaptations */
@media (max-width: 960px) {
  .custom-navigation {
    position: fixed !important;
    z-index: 1200 !important;
    height: auto !important;
    max-height: 70vh;
    bottom: 0;
    top: auto;
    border-top: 1px solid rgba(var(--v-theme-on-surface), 0.12);
    border-right: none;
    border-radius: 16px 16px 0 0;
  }
  
  .main-nav-list {
    max-height: 50vh;
    overflow-y: auto;
  }
  
  .nav-section-header {
    font-size: 0.7rem;
    margin: 8px 16px 4px 16px;
  }
}

/* Scrollbar personnalisée */
:deep(.v-list) {
  scrollbar-width: thin;
  scrollbar-color: rgba(var(--v-theme-on-surface), 0.2) transparent;
}

:deep(.v-list::-webkit-scrollbar) {
  width: 4px;
}

:deep(.v-list::-webkit-scrollbar-track) {
  background: transparent;
}

:deep(.v-list::-webkit-scrollbar-thumb) {
  background: rgba(var(--v-theme-on-surface), 0.2);
  border-radius: 2px;
}

:deep(.v-list::-webkit-scrollbar-thumb:hover) {
  background: rgba(var(--v-theme-on-surface), 0.3);
}

/* Hover effects améliorés */
@keyframes pulse {
  0% { box-shadow: 0 0 0 0 rgba(var(--v-theme-primary), 0.4); }
  70% { box-shadow: 0 0 0 8px rgba(var(--v-theme-primary), 0); }
  100% { box-shadow: 0 0 0 0 rgba(var(--v-theme-primary), 0); }
}

.user-profile-item:active .user-avatar {
  animation: pulse 0.6s;
}

/* États de focus améliorés */
:deep(.v-list-item:focus-visible) {
  outline: 2px solid rgba(var(--v-theme-primary), 0.5);
  outline-offset: 2px;
}

/* Thème sombre optimisé */
:deep(.v-theme--dark) {
  .custom-navigation {
    background: linear-gradient(180deg, 
      rgba(18, 18, 18, 0.98) 0%, 
      rgba(24, 24, 24, 0.95) 100%
    ) !important;
  }
  
  .user-menu-card {
    background: rgba(30, 30, 30, 0.98) !important;
  }
}

/* Performance optimizations */
.nav-item,
.user-profile-item,
.nav-header {
  will-change: transform;
}

/* Accessibilité */
@media (prefers-reduced-motion: reduce) {
  .nav-item,
  .user-profile-item,
  .nav-header,
  .rail-toggle-btn,
  .logo-avatar {
    transition: none !important;
    animation: none !important;
  }
}

/* Print styles */
@media print {
  .custom-navigation {
    display: none !important;
  }
}
</style>
