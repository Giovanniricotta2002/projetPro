<template>
  <div class="navigation-group">
    <v-list-item
      :to="mainPath"
      class="group-header"
      :class="{ 'active': active }"
      rounded="lg"
    >
      <template #prepend>
        <v-avatar 
          size="36" 
          :color="active ? 'primary' : 'surface-variant'"
          class="group-avatar"
        >
          <v-icon size="20">{{ icon }}</v-icon>
        </v-avatar>
      </template>

      <v-list-item-title class="group-title">
        {{ title }}
      </v-list-item-title>

      <template #append>
        <div class="d-flex align-center">
          <v-badge
            v-if="badge && badge > 0"
            :content="badge"
            :color="badgeColor"
            inline
            class="me-2"
          />
          <v-btn
            :icon="expanded ? 'mdi-chevron-up' : 'mdi-chevron-down'"
            variant="text"
            size="small"
            @click.prevent="toggleExpanded"
          />
        </div>
      </template>
    </v-list-item>

    <v-expand-transition>
      <div v-if="expanded" class="group-items">
        <v-list-item
          v-for="item in items"
          :key="item.path"
          :to="item.path"
          class="group-sub-item"
          rounded="lg"
        >
          <template #prepend>
            <v-icon 
              size="18" 
              :color="isActiveItem(item.path) ? 'primary' : 'surface-variant'"
            >
              {{ item.icon }}
            </v-icon>
          </template>

          <v-list-item-title class="sub-item-title">
            {{ item.title }}
          </v-list-item-title>

          <template #append>
            <v-badge
              v-if="item.badge && item.badge > 0"
              :content="item.badge"
              :color="item.badgeColor || 'primary'"
              inline
              class="me-1"
            />
            <v-icon
              v-if="item.status === 'online'"
              color="success"
              size="12"
            >
              mdi-circle
            </v-icon>
            <v-icon
              v-else-if="item.status === 'offline'"
              color="error"
              size="12"
            >
              mdi-circle
            </v-icon>
          </template>
        </v-list-item>
      </div>
    </v-expand-transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'

interface NavigationItem {
  title: string
  path: string
  icon: string
  badge?: number
  badgeColor?: string
  status?: 'online' | 'offline'
}

interface Props {
  items: NavigationItem[]
  icon: string
  title: string
  active?: boolean
  badge?: number
  badgeColor?: string
  mainPath?: string
}

const props = withDefaults(defineProps<Props>(), {
  active: false,
  badge: 0,
  badgeColor: 'primary',
  mainPath: ''
})

const route = useRoute()
const expanded = ref(true)

const isActiveItem = (path: string) => {
  return route.path === path || route.path.startsWith(path + '/')
}

const toggleExpanded = () => {
  expanded.value = !expanded.value
}
</script>

<style scoped>
.navigation-group {
  margin: 8px 0;
}

.group-header {
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  padding: 12px 16px;
  margin: 4px 0;
  background: linear-gradient(
    90deg,
    rgba(var(--v-theme-surface-variant), 0.3) 0%,
    rgba(var(--v-theme-surface-variant), 0.1) 100%
  );
}

.group-header:hover {
  transform: translateX(4px);
  background: linear-gradient(
    90deg,
    rgba(var(--v-theme-primary), 0.1) 0%,
    rgba(var(--v-theme-primary), 0.05) 100%
  );
  box-shadow: 0 4px 12px rgba(var(--v-theme-primary), 0.1);
}

.group-header.active {
  background: linear-gradient(
    90deg,
    rgba(var(--v-theme-primary), 0.15) 0%,
    rgba(var(--v-theme-primary), 0.08) 100%
  );
  border-left: 3px solid rgb(var(--v-theme-primary));
}

.group-avatar {
  transition: all 0.3s ease;
  border: 2px solid rgba(var(--v-theme-outline), 0.1);
}

.group-header:hover .group-avatar {
  transform: scale(1.05);
  border-color: rgba(var(--v-theme-primary), 0.3);
}

.group-title {
  font-weight: 500;
  font-size: 0.95rem;
}

.group-items {
  background: rgba(var(--v-theme-surface-variant), 0.05);
  border-radius: 8px;
  margin: 4px 0 8px 0;
  padding: 4px 0;
}

.group-sub-item {
  margin: 2px 12px;
  padding: 8px 12px;
  transition: all 0.2s ease;
  background: transparent;
  min-height: 40px;
}

.group-sub-item:hover {
  transform: translateX(6px);
  background: rgba(var(--v-theme-primary), 0.05);
}

.group-sub-item.v-list-item--active {
  background: rgba(var(--v-theme-primary), 0.1);
  border-left: 2px solid rgb(var(--v-theme-primary));
}

.sub-item-title {
  font-size: 0.875rem;
  font-weight: 400;
}

/* Badges */
:deep(.v-badge) {
  --v-badge-size: 16px;
}

:deep(.v-badge .v-badge__badge) {
  font-size: 0.7rem;
  font-weight: 600;
  min-width: 16px;
  height: 16px;
}
</style>
