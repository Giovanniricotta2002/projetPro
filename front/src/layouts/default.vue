<template>
  <div class="app-layout">
    <!-- Navigation sidebar -->
    <Navigation v-if="showNavigation" />
    
    <!-- Main content area -->
    <main class="main-content" :class="{ 'with-nav': showNavigation }">
      <div class="content-wrapper">
        <!-- Breadcrumb -->
        <nav class="breadcrumb" v-if="showNavigation">
          <ol class="breadcrumb-list">
            <li v-for="(crumb, index) in breadcrumbs" :key="index" class="breadcrumb-item">
              <router-link v-if="crumb.path && index < breadcrumbs.length - 1" :to="crumb.path" class="breadcrumb-link">
                {{ crumb.title }}
              </router-link>
              <span v-else class="breadcrumb-current">{{ crumb.title }}</span>
              <i v-if="index < breadcrumbs.length - 1" class="breadcrumb-separator">›</i>
            </li>
          </ol>
        </nav>

        <!-- Page content -->
        <div class="page-content">
          <router-view />
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Navigation from '@/components/Navigation.vue'

const route = useRoute()
const authStore = useAuthStore()

// Afficher la navigation seulement si l'utilisateur est connecté
const showNavigation = computed(() => {
  return authStore.isAuthenticated && route.name !== 'login' && route.name !== 'register'
})

// Génération automatique du breadcrumb
const breadcrumbs = computed(() => {
  const crumbs = [{ title: 'Accueil', path: '/' }]
  
  if (route.matched.length > 1) {
    route.matched.forEach((match, index) => {
      if (match.meta?.title && index > 0) {
        crumbs.push({
          title: match.meta.title as string,
          path: index === route.matched.length - 1 ? undefined : match.path
        })
      }
    })
  }
  
  return crumbs
})
</script>

<style scoped>
.app-layout {
  display: flex;
  min-height: 100vh;
  background-color: #f8f9fa;
}

.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  transition: margin-left 0.3s ease;
}

.main-content.with-nav {
  margin-left: 250px;
}

.content-wrapper {
  flex: 1;
  padding: 1.5rem;
}

.breadcrumb {
  margin-bottom: 1.5rem;
  padding: 0.75rem 1rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.breadcrumb-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.breadcrumb-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.breadcrumb-link {
  color: #3498db;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.3s ease;
}

.breadcrumb-link:hover {
  color: #2980b9;
  text-decoration: underline;
}

.breadcrumb-current {
  color: #2c3e50;
  font-weight: 600;
}

.breadcrumb-separator {
  color: #7f8c8d;
  font-style: normal;
}

.page-content {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  min-height: calc(100vh - 8rem);
}

/* Responsive */
@media (max-width: 768px) {
  .main-content.with-nav {
    margin-left: 0;
  }
  
  .content-wrapper {
    padding: 1rem;
  }
  
  .page-content {
    padding: 1rem;
    min-height: auto;
  }
  
  .breadcrumb {
    margin-bottom: 1rem;
    padding: 0.5rem;
  }
  
  .breadcrumb-list {
    flex-wrap: wrap;
  }
}

@media (max-width: 480px) {
  .content-wrapper {
    padding: 0.5rem;
  }
  
  .page-content {
    padding: 0.75rem;
    border-radius: 8px;
  }
}
</style>
