<template>
  <v-app>
    <v-container class="d-flex flex-column align-center justify-center" style="min-height: 90vh;">
      <v-card class="pa-6" elevation="3" max-width="2400">
      <v-row class="mb-2" align="center" justify="space-between">
        <v-col cols="10">
          <v-card-title class="headline text-center">{{ machine && machine.name || '' }}</v-card-title>
        </v-col>
        <v-col cols="2" class="d-flex justify-end">
          <v-btn color="primary" @click="$router.push('/materiels')" flat variant="text">Retour à la liste</v-btn>
        </v-col>
      </v-row>
        <div class="machine-cercle" v-if="machine && machine.infoMachines">
          <!-- SVG pour les traits entre l'image et les bulles -->
          <svg class="bulle-lines" width="800" height="800" viewBox="0 0 800 800" style="position:absolute;top:0;left:0;pointer-events:none;z-index:0;">
            <line v-for="(bulle, i) in machine.infoMachines" :key="'line-'+bulle.id"
              :x1="400" :y1="400"
              :x2="getBullePos(i).x" :y2="getBullePos(i).y"
              stroke="#1976d2" stroke-width="2" stroke-linecap="round" />
          </svg>
          <v-avatar size="100" class="machine-img">
            <v-img :src="machine.image" :alt="machine.name" width="100%" height="100%" cover />
          </v-avatar>
          <transition-group name="bulle-fade" tag="div">
            <div v-for="(bulle, i) in machine.infoMachines" :key="bulle.id" :class="['bulle', bulle.type]"
              :style="bulleStyle(i, showBulles[i])">
              <span>{{ bulle.text }}</span>
            </div>
          </transition-group>
        </div>
      </v-card>
    </v-container>
  </v-app>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import type { InfoMachine } from '@/types/InfoMachine'
import type { CSSProperties } from 'vue'
import type { Machine } from '@/types/Machine'

const route = useRoute()
const authStore = useAuthStore()

const machine = ref<Machine | null>(null)
const showBulles = ref<boolean[]>([])

async function fetchMachine() {
  const materielId = route.params.materielId
  try {
    const response = await authStore.apiRequest<Machine>(`/api/machines/${materielId}`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' },
    })
    if (response.success && response.data) {
      machine.value = response.data
      showBulles.value = Array(machine.value?.infoMachines.length || 0).fill(false)
      // Animation bulles après chargement
      machine.value?.infoMachines.forEach((_, i) => {
        setTimeout(() => showBulles.value[i] = true, 200 + i * 120)
      })
    } else {
      machine.value = null
      showBulles.value = []
    }
  } catch (e) {
    machine.value = null
    showBulles.value = []
  }
}

onMounted(fetchMachine)

function getBullePos(i: number) {
  const n = machine.value?.infoMachines.length || 0
  const angle = (2 * Math.PI * i) / n - Math.PI / 2
  const r = 300
  const x = 400 + r * Math.cos(angle)
  const y = 400 + r * Math.sin(angle)
  return { x, y }
}

function bulleStyle(i: number, visible: boolean): CSSProperties {
  const { x, y } = getBullePos(i)
  return {
    position: 'absolute',
    left: `${visible ? x - 90 : 400 - 90}px`,
    top: `${visible ? y - 48 : 400 - 48}px`,
    opacity: visible ? 1 : 0,
    zIndex: 3,
    transition: 'all 0.6s cubic-bezier(.4,2,.6,1)',
  }
}
</script>

<style scoped>
.machine-cercle {
  position: relative;
  width: 800px;
  height: 800px;
  margin: 0 auto;
}
.bulle-lines {
  z-index: 0;
}
.machine-img {
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  background: #f5f5f5;
  border: 3px solid #1976d2;
  overflow: hidden;
  z-index: 2;
}
.bulle {
  width: 180px;
  height: 96px;
  background: rgba(227,242,253,0.95);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 1.25em;
  color: #15406a;
  box-shadow: 0 2px 8px #0001;
  text-align: center;
  padding: 0 10px;
  letter-spacing: 0.01em;
  text-shadow: 0 1px 2px #fff, 0 0 2px #1976d2;
}

.bulle.usage {
  background: #e3f2fd;
  color: #1976d2;
}
.bulle.carac {
  background: #fffde7;
  color: #bfa100;
}
.bulle.confort {
  background: #e8f5e9;
  color: #388e3c;
}
.bulle.sécurité {
  background: #ffebee;
  color: #c62828;
}
.bulle.autre {
  background: #ede7f6;
  color: #6a1b9a;
}

.bulle-fade-enter-active, .bulle-fade-leave-active {
  transition: opacity 0.5s;
}
.bulle-fade-enter-from, .bulle-fade-leave-to {
  opacity: 0;
}

@media (max-width: 900px) {
  .machine-cercle {
    width: 400px;
    height: 400px;
  }
  .bulle {
    width: 90px;
    height: 48px;
    font-size: 0.9em;
  }
  .machine-img {
    width: 90px !important;
    height: 90px !important;
  }
}

</style>
