<template>
  <div class="user-profile-card-container pa-2">
    <v-menu 
      location="top" 
      :close-on-content-click="false"
      transition="slide-y-reverse-transition"
      :offset="12"
      :min-width="320"
    >
      <template #activator="{ props }">
        <v-card
          v-bind="props"
          class="user-profile-card"
          :class="{ 'rail-mode': rail }"
          elevation="3"
          rounded="xl"
          hover
        >
          <v-card-item class="pa-3">
            <template #prepend>
              <v-badge
                :model-value="unreadNotifications > 0"
                :content="unreadNotifications"
                color="error"
                offset-x="8"
                offset-y="8"
              >
                <v-avatar 
                  :size="rail ? 40 : 48"
                  class="user-avatar"
                  color="primary"
                >
                  <v-img 
                    v-if="authStore.user?.avatar"
                    :src="authStore.user.avatar"
                    :alt="authStore.user?.username"
                  />
                  <v-icon 
                    v-else
                    :size="rail ? 20 : 24"
                    color="white"
                  >
                    mdi-account
                  </v-icon>
                </v-avatar>
              </v-badge>
            </template>
            
            <v-card-title 
              v-if="!rail"
              class="user-name pa-0"
            >
              {{ authStore.user?.username || 'Utilisateur' }}
            </v-card-title>
            
            <v-card-subtitle 
              v-if="!rail"
              class="user-role pa-0"
            >
              {{ getUserRole() }}
            </v-card-subtitle>

            <template #append v-if="!rail">
              <div class="d-flex flex-column align-center">
                <v-chip
                  :color="getRoleColor()"
                  size="x-small"
                  variant="flat"
                  class="mb-1"
                >
                  {{ getUserBadge() }}
                </v-chip>
                <v-icon 
                  size="small" 
                  color="medium-emphasis"
                >
                  mdi-chevron-up
                </v-icon>
              </div>
            </template>
          </v-card-item>
        </v-card>
      </template>

      <v-card 
        class="user-menu-card elevation-12"
        rounded="xl"
        max-width="380"
      >
        <!-- En-tête étendu -->
        <v-card-item class="pa-4 user-header">
          <template #prepend>
            <v-avatar 
              size="64" 
              class="user-avatar-large"
              color="primary"
            >
              <v-img 
                v-if="authStore.user?.avatar"
                :src="authStore.user.avatar"
                :alt="authStore.user?.username"
              />
              <v-icon v-else color="white" size="32">mdi-account</v-icon>
            </v-avatar>
          </template>

          <v-card-title class="user-name-large">
            {{ authStore.user?.username || 'Utilisateur' }}
          </v-card-title>

          <v-card-subtitle class="user-role-large">
            {{ getUserRole() }}
          </v-card-subtitle>

          <template #append>
            <div class="d-flex flex-column align-end">
              <v-chip
                :color="getRoleColor()"
                size="small"
                variant="flat"
                class="mb-2"
              >
                {{ getUserBadge() }}
              </v-chip>
              <div class="d-flex align-center">
                <v-icon 
                  :color="onlineStatus ? 'success' : 'warning'"
                  size="12"
                  class="me-1"
                >
                  mdi-circle
                </v-icon>
                <span class="text-caption">
                  {{ onlineStatus ? 'En ligne' : 'Absent' }}
                </span>
              </div>
            </div>
          </template>
        </v-card-item>

        <!-- Statistiques utilisateur -->
        <v-card-text class="pa-0">
          <v-row dense class="ma-0 stats-row">
            <v-col cols="4" class="pa-2">
              <div class="stat-item">
                <div class="stat-value text-primary">
                  {{ userStats.posts }}
                </div>
                <div class="stat-label">Posts</div>
              </div>
            </v-col>
            <v-col cols="4" class="pa-2">
              <div class="stat-item">
                <div class="stat-value text-success">
                  {{ userStats.likes }}
                </div>
                <div class="stat-label">Likes</div>
              </div>
            </v-col>
            <v-col cols="4" class="pa-2">
              <div class="stat-item">
                <div class="stat-value text-info">
                  #{{ userStats.rank }}
                </div>
                <div class="stat-label">Rang</div>
              </div>
            </v-col>
          </v-row>
        </v-card-text>

        <v-divider />

        <!-- Actions rapides -->
        <v-card-text class="pa-2">
          <v-list density="compact" class="pa-0" nav>
            <v-list-item
              to="/profile"
              prepend-icon="mdi-account"
              title="Mon profil"
              class="user-menu-item"
              rounded="lg"
            >
              <template #append>
                <v-icon size="16" color="medium-emphasis">
                  mdi-chevron-right
                </v-icon>
              </template>
            </v-list-item>

            <v-list-item
              to="/profile/settings"
              prepend-icon="mdi-cog"
              title="Paramètres"
              class="user-menu-item"
              rounded="lg"
            >
              <template #append>
                <v-icon size="16" color="medium-emphasis">
                  mdi-chevron-right
                </v-icon>
              </template>
            </v-list-item>

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

            <v-list-item
              prepend-icon="mdi-palette"
              title="Thème"
              class="user-menu-item"
              rounded="lg"
              @click="toggleTheme"
            >
              <template #append>
                <v-icon 
                  :icon="theme.global.name.value === 'dark' ? 'mdi-weather-night' : 'mdi-weather-sunny'"
                  size="16" 
                  color="medium-emphasis"
                />
              </template>
            </v-list-item>
          </v-list>
        </v-card-text>

        <v-divider />

        <!-- Actions de session -->
        <v-card-actions class="pa-3">
          <v-btn
            to="/profile"
            variant="outlined"
            color="primary"
            block
            class="me-2"
          >
            <v-icon start>mdi-account</v-icon>
            Profil
          </v-btn>
          
          <v-btn
            to="/logout"
            variant="tonal"
            color="error"
            block
          >
            <v-icon start>mdi-logout</v-icon>
            Déconnexion
          </v-btn>
        </v-card-actions>

        <!-- Footer avec version -->
        <v-card-text class="pa-2 pt-0">
          <div class="text-center">
            <div class="text-caption text-medium-emphasis">
              MuscuScope v{{ appVersion }}
            </div>
            <div class="text-caption text-medium-emphasis">
              Dernière connexion: {{ lastLogin }}
            </div>
          </div>
        </v-card-text>
      </v-card>
    </v-menu>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useTheme } from 'vuetify'

interface Props {
  rail?: boolean
  isMobile?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  rail: false,
  isMobile: false
})

const emit = defineEmits<{
  toggleRail: []
}>()

const authStore = useAuthStore()
const theme = useTheme()

// États réactifs
const unreadNotifications = ref(5)
const onlineStatus = ref(true)
const appVersion = ref('1.2.0')
const lastLogin = ref('Il y a 2 heures')

// Statistiques utilisateur simulées
const userStats = ref({
  posts: 42,
  likes: 128,
  rank: 15
})

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

const toggleTheme = () => {
  theme.global.name.value = theme.global.name.value === 'dark' ? 'light' : 'dark'
}

// Simuler le statut en ligne
onMounted(() => {
  setInterval(() => {
    onlineStatus.value = Math.random() > 0.1 // 90% de chance d'être en ligne
  }, 30000) // Vérifier toutes les 30 secondes
})
</script>

<style scoped>
.user-profile-card-container {
  position: sticky;
  bottom: 0;
  background: linear-gradient(
    to top,
    rgba(var(--v-theme-surface), 1) 0%,
    rgba(var(--v-theme-surface), 0.9) 100%
  );
  backdrop-filter: blur(10px);
}

.user-profile-card {
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  background: linear-gradient(
    135deg,
    rgba(var(--v-theme-primary), 0.05) 0%,
    rgba(var(--v-theme-primary), 0.02) 100%
  );
  border: 1px solid rgba(var(--v-theme-primary), 0.1);
}

.user-profile-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 32px rgba(var(--v-theme-primary), 0.2);
  border-color: rgba(var(--v-theme-primary), 0.3);
}

.user-profile-card.rail-mode {
  .user-name,
  .user-role {
    opacity: 0;
    transform: translateX(-10px);
  }
}

.user-avatar {
  transition: all 0.3s ease;
  border: 3px solid rgba(var(--v-theme-primary), 0.2);
  box-shadow: 0 4px 12px rgba(var(--v-theme-primary), 0.15);
}

.user-profile-card:hover .user-avatar {
  transform: scale(1.05);
  border-color: rgba(var(--v-theme-primary), 0.4);
  box-shadow: 0 6px 16px rgba(var(--v-theme-primary), 0.25);
}

.user-name {
  font-size: 1rem;
  font-weight: 600;
  color: rgb(var(--v-theme-on-surface));
}

.user-role {
  font-size: 0.85rem;
  opacity: 0.8;
}

/* Menu utilisateur */
.user-menu-card {
  background: rgba(var(--v-theme-surface), 0.98) !important;
  backdrop-filter: blur(20px);
  border: 1px solid rgba(var(--v-theme-outline), 0.08);
  overflow: hidden;
}

.user-header {
  background: linear-gradient(
    135deg,
    rgba(var(--v-theme-primary), 0.05) 0%,
    rgba(var(--v-theme-surface-variant), 0.03) 100%
  );
  border-bottom: 1px solid rgba(var(--v-theme-outline), 0.05);
}

.user-avatar-large {
  border: 4px solid rgba(var(--v-theme-primary), 0.3);
  box-shadow: 0 6px 20px rgba(var(--v-theme-primary), 0.2);
  transition: all 0.3s ease;
}

.user-avatar-large:hover {
  transform: scale(1.05) rotate(2deg);
}

.user-name-large {
  font-size: 1.1rem;
  font-weight: 700;
  background: linear-gradient(45deg, 
    rgb(var(--v-theme-primary)), 
    rgb(var(--v-theme-secondary))
  );
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.user-role-large {
  font-size: 0.9rem;
  font-weight: 500;
  opacity: 0.8;
}

/* Statistiques */
.stats-row {
  background: rgba(var(--v-theme-surface-variant), 0.3);
}

.stat-item {
  text-align: center;
  padding: 8px;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.stat-item:hover {
  background: rgba(var(--v-theme-primary), 0.05);
  transform: translateY(-2px);
}

.stat-value {
  font-size: 1.25rem;
  font-weight: 700;
  line-height: 1.2;
}

.stat-label {
  font-size: 0.75rem;
  opacity: 0.7;
  margin-top: 2px;
}

/* Menu items */
.user-menu-item {
  margin: 2px 0;
  transition: all 0.2s ease;
  min-height: 40px;
}

.user-menu-item:hover {
  background: rgba(var(--v-theme-primary), 0.08);
  transform: translateX(4px);
}

/* Badges */
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

/* Animations */
@keyframes pulse {
  0%, 100% { 
    box-shadow: 0 0 0 0 rgba(var(--v-theme-primary), 0.4); 
  }
  50% { 
    box-shadow: 0 0 0 8px rgba(var(--v-theme-primary), 0); 
  }
}

.user-profile-card:active .user-avatar {
  animation: pulse 0.6s;
}

/* Responsive */
@media (max-width: 600px) {
  .user-menu-card {
    max-width: 300px !important;
  }
  
  .user-name-large {
    font-size: 1rem;
  }
  
  .stat-value {
    font-size: 1.1rem;
  }
}

/* Transitions */
.slide-y-reverse-transition-enter-active,
.slide-y-reverse-transition-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-y-reverse-transition-enter-from {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}

.slide-y-reverse-transition-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}

/* Thème sombre */
.v-theme--dark .user-menu-card {
  background: rgba(30, 30, 30, 0.98) !important;
  border-color: rgba(255, 255, 255, 0.08);
}

.v-theme--dark .user-header {
  background: linear-gradient(
    135deg,
    rgba(var(--v-theme-primary), 0.08) 0%,
    rgba(66, 66, 66, 0.05) 100%
  );
}

.v-theme--dark .stats-row {
  background: rgba(66, 66, 66, 0.3);
}

/* Accessibilité */
@media (prefers-reduced-motion: reduce) {
  .user-avatar,
  .user-avatar-large,
  .user-profile-card,
  .stat-item {
    transition: none !important;
    animation: none !important;
  }
}
</style>
