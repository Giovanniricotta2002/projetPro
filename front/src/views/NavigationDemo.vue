<template>
  <v-app>
    <!-- Navigation moderne -->
    <NavigationAdvanced v-if="useAdvanced" />
    <Navigation v-else />

    <!-- Contenu principal -->
    <v-main>
      <v-container fluid class="pa-6">
        <v-row>
          <v-col cols="12">
            <v-card class="mb-6" elevation="2" rounded="lg">
              <v-card-title class="d-flex align-center">
                <v-icon class="me-3" color="primary">mdi-cog</v-icon>
                Démonstration Navigation Vuetify 3
              </v-card-title>
              <v-card-text>
                <v-row align="center">
                  <v-col cols="auto">
                    <v-switch
                      v-model="useAdvanced"
                      color="primary"
                      label="Navigation Avancée"
                      hide-details
                    />
                  </v-col>
                  <v-col>
                    <span class="text-medium-emphasis">
                      {{ useAdvanced ? 'Version avancée avec animations et composants modulaires' : 'Version standard optimisée' }}
                    </span>
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>

        <v-row>
          <v-col cols="12" md="6">
            <v-card elevation="2" rounded="lg">
              <v-card-title>
                <v-icon class="me-2" color="success">mdi-check-circle</v-icon>
                Fonctionnalités Implémentées
              </v-card-title>
              <v-card-text>
                <v-list density="compact">
                  <v-list-item
                    v-for="feature in implementedFeatures"
                    :key="feature"
                    prepend-icon="mdi-check"
                  >
                    <v-list-item-title>{{ feature }}</v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-card-text>
            </v-card>
          </v-col>

          <v-col cols="12" md="6">
            <v-card elevation="2" rounded="lg">
              <v-card-title>
                <v-icon class="me-2" color="info">mdi-information</v-icon>
                Composants Vuetify 3 Utilisés
              </v-card-title>
              <v-card-text>
                <v-chip-group column>
                  <v-chip
                    v-for="component in vuetifyComponents"
                    :key="component.name"
                    :color="component.color"
                    variant="outlined"
                    size="small"
                  >
                    <v-icon start size="16">{{ component.icon }}</v-icon>
                    {{ component.name }}
                  </v-chip>
                </v-chip-group>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>

        <v-row class="mt-4">
          <v-col cols="12">
            <v-card elevation="2" rounded="lg">
              <v-card-title>
                <v-icon class="me-2" color="warning">mdi-palette</v-icon>
                Thèmes et Personnalisation
              </v-card-title>
              <v-card-text>
                <v-row>
                  <v-col cols="12" sm="6" md="3">
                    <v-btn
                      @click="toggleTheme"
                      variant="outlined"
                      block
                      :prepend-icon="theme.global.name.value === 'dark' ? 'mdi-weather-night' : 'mdi-weather-sunny'"
                    >
                      {{ theme.global.name.value === 'dark' ? 'Mode Sombre' : 'Mode Clair' }}
                    </v-btn>
                  </v-col>
                  <v-col cols="12" sm="6" md="3">
                    <v-select
                      v-model="selectedColor"
                      :items="colorOptions"
                      label="Couleur Primaire"
                      variant="outlined"
                      density="compact"
                      @update:model-value="changeThemeColor"
                    />
                  </v-col>
                  <v-col cols="12" sm="6" md="3">
                    <v-btn
                      @click="resetDemo"
                      variant="tonal"
                      color="error"
                      block
                      prepend-icon="mdi-refresh"
                    >
                      Réinitialiser
                    </v-btn>
                  </v-col>
                  <v-col cols="12" sm="6" md="3">
                    <v-btn
                      @click="exportConfig"
                      variant="tonal"
                      color="success"
                      block
                      prepend-icon="mdi-download"
                    >
                      Exporter Config
                    </v-btn>
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </v-main>

    <!-- Snackbar pour les notifications -->
    <v-snackbar
      v-model="showSnackbar"
      :timeout="3000"
      location="bottom right"
      :color="snackbarColor"
    >
      {{ snackbarMessage }}
      <template #actions>
        <v-btn
          color="white"
          variant="text"
          @click="showSnackbar = false"
        >
          Fermer
        </v-btn>
      </template>
    </v-snackbar>
  </v-app>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useTheme } from 'vuetify'
import Navigation from '@/components/Navigation.vue'
import NavigationAdvanced from '@/components/NavigationAdvanced.vue'

const theme = useTheme()

// États réactifs
const useAdvanced = ref(false)
const selectedColor = ref('primary')
const showSnackbar = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

// Données
const implementedFeatures = [
  'Navigation drawer responsive',
  'Mode rail avec expand-on-hover',
  'Sous-menus hiérarchiques',
  'Badges de notification dynamiques',
  'Menu utilisateur avancé',
  'Gestion des rôles et permissions',
  'Thème sombre/clair',
  'Animations fluides',
  'États actifs visuels',
  'Recherche intégrée (version avancée)',
  'Statistiques utilisateur',
  'FAB mobile'
]

const vuetifyComponents = [
  { name: 'v-navigation-drawer', icon: 'mdi-menu', color: 'primary' },
  { name: 'v-list', icon: 'mdi-format-list-bulleted', color: 'info' },
  { name: 'v-list-group', icon: 'mdi-folder', color: 'success' },
  { name: 'v-avatar', icon: 'mdi-account-circle', color: 'warning' },
  { name: 'v-badge', icon: 'mdi-numeric', color: 'error' },
  { name: 'v-menu', icon: 'mdi-dots-vertical', color: 'purple' },
  { name: 'v-card', icon: 'mdi-card', color: 'teal' },
  { name: 'v-chip', icon: 'mdi-label', color: 'orange' },
  { name: 'v-fab', icon: 'mdi-plus-circle', color: 'pink' },
  { name: 'v-expansion-panels', icon: 'mdi-arrow-expand', color: 'indigo' }
]

const colorOptions = [
  { title: 'Primaire', value: 'primary' },
  { title: 'Secondaire', value: 'secondary' },
  { title: 'Succès', value: 'success' },
  { title: 'Info', value: 'info' },
  { title: 'Attention', value: 'warning' },
  { title: 'Erreur', value: 'error' }
]

// Fonctions
const toggleTheme = () => {
  theme.global.name.value = theme.global.name.value === 'dark' ? 'light' : 'dark'
  showNotification(
    `Thème ${theme.global.name.value === 'dark' ? 'sombre' : 'clair'} activé`,
    'info'
  )
}

const changeThemeColor = (color: string) => {
  // Dans un vrai projet, vous changeriez les variables CSS du thème
  showNotification(`Couleur ${color} sélectionnée`, 'success')
}

const resetDemo = () => {
  useAdvanced.value = false
  selectedColor.value = 'primary'
  theme.global.name.value = 'light'
  showNotification('Configuration réinitialisée', 'warning')
}

const exportConfig = () => {
  const config = {
    navigationMode: useAdvanced.value ? 'advanced' : 'standard',
    theme: theme.global.name.value,
    primaryColor: selectedColor.value,
    timestamp: new Date().toISOString()
  }
  
  const blob = new Blob([JSON.stringify(config, null, 2)], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'navigation-config.json'
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  URL.revokeObjectURL(url)
  
  showNotification('Configuration exportée', 'success')
}

const showNotification = (message: string, color: string = 'success') => {
  snackbarMessage.value = message
  snackbarColor.value = color
  showSnackbar.value = true
}
</script>

<style scoped>
.v-main {
  background: linear-gradient(
    135deg,
    rgba(var(--v-theme-surface), 1) 0%,
    rgba(var(--v-theme-surface-variant), 0.3) 100%
  );
  min-height: 100vh;
}

.v-card {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.v-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
}

.v-chip {
  transition: all 0.2s ease;
}

.v-chip:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
</style>
