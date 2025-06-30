<template>
  <div>
    <!-- Navigation Drawer Principal -->
    <v-navigation-drawer
      v-model="drawer"
      :rail="rail && !isMobile"
      :permanent="!isMobile"
      :location="isMobile ? 'bottom' : 'left'"
      :temporary="isMobile"
      class="advanced-navigation"
      color="surface"
      :width="320"
      :rail-width="80"
      expand-on-hover
      :elevation="isMobile ? 8 : 2"
    >
      <!-- Header avec animation -->
      <div class="nav-header-container">
        <v-card
          class="nav-header-card ma-2"
          :class="{ 'rail-mode': rail && !isMobile }"
          elevation="2"
          rounded="xl"
          @click="!isMobile && toggleRail()"
        >
          <v-card-item class="pa-3">
            <template #prepend>
              <v-avatar 
                :size="rail && !isMobile ? 48 : 56"
                class="logo-avatar"
                color="primary"
              >
                <v-img 
                  src="/logo.png" 
                  alt="MuscuScope"
                  @error="showFallbackIcon = true"
                  v-if="!showFallbackIcon"
                />
                <v-icon 
                  v-else
                  :size="rail && !isMobile ? 24 : 28"
                  color="white"
                >
                  mdi-dumbbell
                </v-icon>
              </v-avatar>
            </template>

            <v-card-title 
              v-show="!rail || isMobile"
              class="app-title pa-0"
            >
              MuscuScope
            </v-card-title>

            <v-card-subtitle 
              v-show="!rail || isMobile"
              class="app-subtitle pa-0"
            >
              Gestion Musculation
            </v-card-subtitle>

            <template #append>
              <v-btn
                v-if="!isMobile"
                :icon="rail ? 'mdi-menu-open' : 'mdi-menu'"
                variant="text"
                size="small"
                color="primary"
                class="rail-toggle"
                @click.stop="toggleRail()"
              />
            </template>
          </v-card-item>
        </v-card>
      </div>

      <!-- Barre de recherche -->
      <div v-if="!rail || isMobile" class="search-container pa-2">
        <v-text-field
          v-model="searchQuery"
          prepend-inner-icon="mdi-magnify"
          placeholder="Rechercher..."
          variant="outlined"
          density="compact"
          rounded="lg"
          hide-details
          clearable
          class="search-field"
          @keyup.enter="performSearch"
        >
          <template #append-inner>
            <v-fade-transition>
              <v-progress-circular
                v-if="isSearching"
                size="16"
                width="2"
                indeterminate
                color="primary"
              />
            </v-fade-transition>
          </template>
        </v-text-field>
      </div>

      <!-- Navigation avec animations -->
      <v-list 
        class="navigation-list pa-2"
        nav
        density="comfortable"
        :lines="false"
        color="primary"
      >
        <!-- Raccourcis rapides -->
        <div class="quick-actions mb-4">
          <v-chip-group 
            v-if="!rail || isMobile"
            class="px-2"
            column
          >
            <v-chip
              v-for="shortcut in quickShortcuts"
              :key="shortcut.name"
              :to="shortcut.path"
              :color="shortcut.color"
              variant="outlined"
              size="small"
              class="quick-chip"
            >
              <v-icon size="16" start>{{ shortcut.icon }}</v-icon>
              {{ shortcut.name }}
            </v-chip>
          </v-chip-group>
        </div>

        <!-- Menu principal avec animations -->
        <v-expansion-panels
          v-model="expandedPanels"
          multiple
          variant="accordion"
          class="nav-panels"
        >
          <!-- Dashboard -->
          <v-list-item
            :to="'/'"
            class="nav-main-item mb-2"
            rounded="xl"
            :active="isCurrentPage('/')"
          >
            <template #prepend>
              <v-avatar 
                size="40" 
                :color="isCurrentPage('/') ? 'primary' : 'surface-variant'"
                class="item-avatar"
              >
                <v-icon>mdi-view-dashboard</v-icon>
              </v-avatar>
            </template>

            <v-list-item-title class="nav-title">
              Tableau de bord
            </v-list-item-title>

            <template #append>
              <v-fade-transition>
                <v-icon 
                  v-if="isCurrentPage('/')"
                  color="primary"
                  size="20"
                >
                  mdi-check-circle
                </v-icon>
              </v-fade-transition>
            </template>
          </v-list-item>

          <!-- Section Navigation -->
          <v-expansion-panel value="navigation">
            <v-expansion-panel-title class="section-header">
              <template #default>
                <div class="d-flex align-center">
                  <v-icon class="me-3" color="primary">mdi-compass</v-icon>
                  <span class="section-title">Navigation</span>
                </div>
              </template>
            </v-expansion-panel-title>
            
            <v-expansion-panel-text class="pa-0">
              <!-- Machines -->
              <NavigationGroup
                :items="machinesMenuItems"
                icon="mdi-cog"
                title="Machines"
                :active="isMachinesActive"
                :badge="machinesCount"
                badge-color="info"
              />

              <!-- Forum -->
              <NavigationGroup
                :items="forumMenuItems"
                icon="mdi-forum"
                title="Forum"
                :active="isForumActive"
                :badge="unreadMessages"
                badge-color="warning"
              />
            </v-expansion-panel-text>
          </v-expansion-panel>

          <!-- Section Analyse -->
          <v-expansion-panel value="analytics">
            <v-expansion-panel-title class="section-header">
              <template #default>
                <div class="d-flex align-center">
                  <v-icon class="me-3" color="info">mdi-chart-line</v-icon>
                  <span class="section-title">Analyse</span>
                </div>
              </template>
            </v-expansion-panel-title>
            
            <v-expansion-panel-text class="pa-0">
              <NavigationGroup
                :items="statsMenuItems"
                icon="mdi-chart-bar"
                title="Statistiques"
                :active="isStatsActive"
              />
            </v-expansion-panel-text>
          </v-expansion-panel>

          <!-- Section Gestion -->
          <v-expansion-panel 
            v-if="hasSpecialRoles"
            value="management"
          >
            <v-expansion-panel-title class="section-header">
              <template #default>
                <div class="d-flex align-center">
                  <v-icon class="me-3" color="warning">mdi-shield-crown</v-icon>
                  <span class="section-title">Gestion</span>
                </div>
              </template>
            </v-expansion-panel-title>
            
            <v-expansion-panel-text class="pa-0">
              <!-- Modération -->
              <NavigationGroup
                v-if="authStore.hasRole('moderator') || authStore.hasRole('admin')"
                :items="moderationMenuItems"
                icon="mdi-shield-account"
                title="Modération"
                :active="isModerationActive"
                :badge="pendingReports"
                badge-color="error"
              />

              <!-- Administration -->
              <NavigationGroup
                v-if="authStore.hasRole('admin')"
                :items="adminMenuItems"
                icon="mdi-shield-crown"
                title="Administration"
                :active="isAdminActive"
              />
            </v-expansion-panel-text>
          </v-expansion-panel>
        </v-expansion-panels>
      </v-list>

      <!-- Footer utilisateur avancé -->
      <template #append>
        <UserProfileCard 
          :rail="rail && !isMobile"
          :is-mobile="isMobile"
          @toggle-rail="toggleRail"
        />
      </template>
    </v-navigation-drawer>

    <!-- Overlay pour mobile -->
    <v-overlay
      v-if="isMobile && drawer"
      :model-value="drawer"
      class="align-center justify-center"
      @click="drawer = false"
    />

    <!-- FAB flottant pour mobile -->
    <v-fab
      v-if="isMobile && !drawer"
      icon="mdi-menu"
      location="bottom end"
      size="large"
      color="primary"
      elevation="8"
      class="mobile-nav-fab"
      @click="drawer = true"
    >
      <v-badge
        v-if="totalNotifications > 0"
        :content="totalNotifications"
        color="error"
        offset-x="4"
        offset-y="4"
      />
    </v-fab>

    <!-- Notification Toast -->
    <v-snackbar
      v-model="showNotification"
      :timeout="3000"
      location="top right"
      color="success"
    >
      {{ notificationMessage }}
      <template #actions>
        <v-btn
          color="white"
          variant="text"
          @click="showNotification = false"
        >
          Fermer
        </v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useDisplay, useTheme } from 'vuetify'
import NavigationGroup from './NavigationGroup.vue'
import UserProfileCard from './UserProfileCard.vue'

const route = useRoute()
const authStore = useAuthStore()
const { mobile } = useDisplay()
const theme = useTheme()

// États réactifs
const drawer = ref(true)
const rail = ref(false)
const searchQuery = ref('')
const isSearching = ref(false)
const expandedPanels = ref(['navigation', 'analytics', 'management'])
const showFallbackIcon = ref(false)
const showNotification = ref(false)
const notificationMessage = ref('')

// Données simulées (à remplacer par de vraies données)
const machinesCount = ref(45)
const unreadMessages = ref(12)
const pendingReports = ref(3)

// Computed properties
const isMobile = computed(() => mobile.value)

const hasSpecialRoles = computed(() => 
  authStore.hasRole('moderator') || authStore.hasRole('editor') || authStore.hasRole('admin')
)

const totalNotifications = computed(() => 
  unreadMessages.value + pendingReports.value
)

// Navigation states
const isCurrentPage = (path: string) => route.path === path

const isMachinesActive = computed(() => route.path.startsWith('/machines'))
const isForumActive = computed(() => route.path.startsWith('/forum'))
const isStatsActive = computed(() => 
  route.path.startsWith('/stats') || route.path.startsWith('/grafana')
)
const isModerationActive = computed(() => route.path.startsWith('/moderation'))
const isAdminActive = computed(() => 
  route.path.startsWith('/admin') || route.path.startsWith('/permissions')
)

// Raccourcis rapides
const quickShortcuts = computed(() => [
  { name: 'Dashboard', path: '/', icon: 'mdi-view-dashboard', color: 'primary' },
  { name: 'Machines', path: '/machines', icon: 'mdi-cog', color: 'info' },
  { name: 'Forum', path: '/forum', icon: 'mdi-forum', color: 'success' },
  { name: 'Stats', path: '/stats', icon: 'mdi-chart-line', color: 'warning' },
])

// Menu items
const machinesMenuItems = computed(() => [
  {
    title: 'Liste complète',
    path: '/machines',
    icon: 'mdi-format-list-bulleted',
  },
  ...(authStore.hasRole('editor') || authStore.hasRole('admin') ? [{
    title: 'Éditer machines',
    path: '/editor/machines',
    icon: 'mdi-pencil',
  }] : [])
])

const forumMenuItems = computed(() => [
  {
    title: 'Catégories',
    path: '/forum',
    icon: 'mdi-folder',
  },
  {
    title: 'Messages récents',
    path: '/forum/recent',
    icon: 'mdi-fire',
    badge: 8,
    badgeColor: 'error'
  }
])

const statsMenuItems = computed(() => [
  {
    title: 'Rapports généraux',
    path: '/stats',
    icon: 'mdi-chart-bar',
  },
  {
    title: 'Logs connexion',
    path: '/stats/login-logs',
    icon: 'mdi-key',
  },
  {
    title: 'Monitoring',
    path: '/grafana',
    icon: 'mdi-monitor-dashboard',
    status: 'online'
  }
])

const moderationMenuItems = computed(() => [
  {
    title: 'Tableau de bord',
    path: '/moderation',
    icon: 'mdi-view-dashboard',
  },
  {
    title: 'Signalements',
    path: '/moderation/reports',
    icon: 'mdi-flag',
    badge: pendingReports.value,
    badgeColor: 'error'
  }
])

const adminMenuItems = computed(() => [
  {
    title: 'Vue d\'ensemble',
    path: '/admin',
    icon: 'mdi-view-dashboard',
  },
  {
    title: 'Utilisateurs',
    path: '/admin/users',
    icon: 'mdi-account-group',
    badge: 156,
    badgeColor: 'info'
  },
  {
    title: 'Droits d\'accès',
    path: '/permissions',
    icon: 'mdi-key-chain',
  },
  {
    title: 'Catégories forum',
    path: '/admin/forum-categories',
    icon: 'mdi-folder-settings',
  },
  {
    title: 'Logs système',
    path: '/admin/logs',
    icon: 'mdi-file-document',
  }
])

// Functions
const toggleRail = () => {
  rail.value = !rail.value
  showNotification.value = true
  notificationMessage.value = rail.value ? 'Mode compact activé' : 'Mode étendu activé'
}

const performSearch = async () => {
  if (!searchQuery.value.trim()) return
  
  isSearching.value = true
  
  // Simuler une recherche
  setTimeout(() => {
    isSearching.value = false
    showNotification.value = true
    notificationMessage.value = `Recherche pour "${searchQuery.value}" terminée`
  }, 1000)
}

// Gestion responsive
const handleResize = () => {
  if (mobile.value) {
    rail.value = false
    drawer.value = false
    expandedPanels.value = []
  } else {
    drawer.value = true
    expandedPanels.value = ['navigation', 'analytics', 'management']
  }
}

// Watchers
watch(route, () => {
  if (mobile.value) {
    drawer.value = false
  }
})

// Lifecycle
onMounted(() => {
  handleResize()
  window.addEventListener('resize', handleResize)
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', handleResize)
})
</script>

<style scoped>
/* Navigation principale */
.advanced-navigation {
  background: linear-gradient(
    180deg,
    rgba(var(--v-theme-surface), 1) 0%,
    rgba(var(--v-theme-surface), 0.98) 100%
  ) !important;
  backdrop-filter: blur(20px);
  border-right: 1px solid rgba(var(--v-theme-outline), 0.12);
}

/* Header avancé */
.nav-header-container {
  position: sticky;
  top: 0;
  z-index: 10;
  background: rgba(var(--v-theme-surface), 0.9);
  backdrop-filter: blur(10px);
}

.nav-header-card {
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  background: linear-gradient(
    135deg,
    rgba(var(--v-theme-primary), 0.05) 0%,
    rgba(var(--v-theme-primary), 0.02) 100%
  );
  border: 1px solid rgba(var(--v-theme-primary), 0.1);
}

.nav-header-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(var(--v-theme-primary), 0.15);
}

.nav-header-card.rail-mode {
  .app-title,
  .app-subtitle {
    opacity: 0;
    transform: translateX(-20px);
  }
}

.logo-avatar {
  transition: all 0.3s ease;
  border: 3px solid rgba(var(--v-theme-primary), 0.2);
}

.nav-header-card:hover .logo-avatar {
  transform: scale(1.05) rotate(5deg);
  border-color: rgba(var(--v-theme-primary), 0.4);
}

.app-title {
  font-size: 1.25rem;
  font-weight: 700;
  background: linear-gradient(45deg, 
    rgb(var(--v-theme-primary)), 
    rgb(var(--v-theme-secondary))
  );
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  transition: all 0.3s ease;
}

.app-subtitle {
  font-size: 0.8rem;
  opacity: 0.7;
  transition: all 0.3s ease;
}

.rail-toggle {
  transition: transform 0.3s ease;
}

.rail-toggle:hover {
  transform: rotate(180deg);
}

/* Recherche */
.search-container {
  position: sticky;
  top: 100px;
  z-index: 9;
  background: rgba(var(--v-theme-surface), 0.9);
  backdrop-filter: blur(10px);
}

.search-field {
  transition: all 0.3s ease;
}

.search-field:focus-within {
  transform: scale(1.02);
}

/* Navigation list */
.navigation-list {
  padding-bottom: 100px;
}

.quick-actions {
  border-bottom: 1px solid rgba(var(--v-theme-outline), 0.08);
  padding-bottom: 16px;
}

.quick-chip {
  transition: all 0.2s ease;
  margin: 2px;
}

.quick-chip:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(var(--v-theme-primary), 0.2);
}

/* Navigation panels */
.nav-panels {
  background: transparent;
}

:deep(.v-expansion-panel) {
  background: transparent;
  margin: 8px 0;
  border-radius: 12px;
  overflow: hidden;
}

:deep(.v-expansion-panel-title) {
  padding: 12px 16px;
  min-height: 56px;
  background: linear-gradient(
    90deg,
    rgba(var(--v-theme-surface-variant), 0.3) 0%,
    rgba(var(--v-theme-surface-variant), 0.1) 100%
  );
}

:deep(.v-expansion-panel-title:hover) {
  background: linear-gradient(
    90deg,
    rgba(var(--v-theme-primary), 0.1) 0%,
    rgba(var(--v-theme-primary), 0.05) 100%
  );
}

.section-header {
  font-weight: 600;
}

.section-title {
  font-size: 0.9rem;
  font-weight: 600;
  letter-spacing: 0.5px;
}

/* Main navigation items */
.nav-main-item {
  margin: 8px 0;
  padding: 12px 16px;
  min-height: 64px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  background: linear-gradient(
    90deg,
    rgba(var(--v-theme-surface-variant), 0.2) 0%,
    rgba(var(--v-theme-surface-variant), 0.05) 100%
  );
}

.nav-main-item:hover {
  transform: translateX(8px);
  background: linear-gradient(
    90deg,
    rgba(var(--v-theme-primary), 0.1) 0%,
    rgba(var(--v-theme-primary), 0.05) 100%
  );
  box-shadow: 0 4px 16px rgba(var(--v-theme-primary), 0.15);
}

.nav-main-item.v-list-item--active {
  background: linear-gradient(
    90deg,
    rgba(var(--v-theme-primary), 0.15) 0%,
    rgba(var(--v-theme-primary), 0.08) 100%
  );
  border-left: 4px solid rgb(var(--v-theme-primary));
}

.item-avatar {
  transition: all 0.3s ease;
  border: 2px solid rgba(var(--v-theme-outline), 0.1);
}

.nav-main-item:hover .item-avatar {
  transform: scale(1.1);
  border-color: rgba(var(--v-theme-primary), 0.3);
}

.nav-title {
  font-weight: 500;
  font-size: 1rem;
}

/* FAB mobile */
.mobile-nav-fab {
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-8px); }
}

/* Responsive */
@media (max-width: 960px) {
  .advanced-navigation {
    position: fixed !important;
    z-index: 1300 !important;
    height: auto !important;
    max-height: 80vh;
    bottom: 0;
    top: auto;
    border-top: 1px solid rgba(var(--v-theme-outline), 0.12);
    border-right: none;
    border-radius: 24px 24px 0 0;
  }
  
  .navigation-list {
    max-height: 60vh;
    overflow-y: auto;
    padding-bottom: 24px;
  }
  
  .nav-header-container {
    position: relative;
  }
  
  .search-container {
    position: relative;
    top: 0;
  }
}

/* Scrollbar */
:deep(.navigation-list) {
  scrollbar-width: thin;
  scrollbar-color: rgba(var(--v-theme-primary), 0.2) transparent;
}

:deep(.navigation-list::-webkit-scrollbar) {
  width: 6px;
}

:deep(.navigation-list::-webkit-scrollbar-track) {
  background: transparent;
}

:deep(.navigation-list::-webkit-scrollbar-thumb) {
  background: rgba(var(--v-theme-primary), 0.2);
  border-radius: 3px;
}

:deep(.navigation-list::-webkit-scrollbar-thumb:hover) {
  background: rgba(var(--v-theme-primary), 0.3);
}

/* Transitions */
.v-navigation-drawer {
  transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Thème sombre */
.v-theme--dark .advanced-navigation {
  background: linear-gradient(
    180deg,
    rgba(18, 18, 18, 0.98) 0%,
    rgba(24, 24, 24, 0.95) 100%
  ) !important;
}

/* Accessibilité */
@media (prefers-reduced-motion: reduce) {
  * {
    animation: none !important;
    transition: none !important;
  }
}

/* États de focus */
:deep(.v-list-item:focus-visible) {
  outline: 2px solid rgba(var(--v-theme-primary), 0.5);
  outline-offset: 2px;
}
</style>
