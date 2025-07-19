<template>
  <v-app>
    <v-container class="d-flex flex-column align-center justify-center" style="min-height: 90vh;">
      <v-card class="pa-6" elevation="3" max-width="2400">
        <v-card-title class="headline text-center mb-4">{{ machine.nom }}</v-card-title>
        <div class="machine-cercle">
          <!-- SVG pour les traits entre l'image et les bulles -->
          <svg class="bulle-lines" width="800" height="800" viewBox="0 0 800 800" style="position:absolute;top:0;left:0;pointer-events:none;z-index:0;">
            <line v-for="(bulle, i) in machine.bulles" :key="'line-'+bulle.id"
              :x1="400" :y1="400"
              :x2="getBullePos(i).x" :y2="getBullePos(i).y"
              stroke="#1976d2" stroke-width="2" stroke-linecap="round" />
          </svg>
          <v-avatar size="100" class="machine-img">
            <v-img :src="machine.image" :alt="machine.nom" width="100%" height="100%" cover />
          </v-avatar>
          <transition-group name="bulle-fade" tag="div">
            <div v-for="(bulle, i) in machine.bulles" :key="bulle.id" :class="['bulle', bulle.type]"
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
import type { InfoMachine } from '@/types/InfoMachine'

const machine = {
  nom: 'Haltère hexagonale',
  image: 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fimages.ctfassets.net%2Fipjoepkmtnha%2F5fQCLXzP2H5BG5VcODED43%2Fc3d521f38437d3181c1705bb9979568f%2Fhex-dumbbell_hero&f=1&nofb=1&ipt=15dc359e6340d1aa00a421d911774013ca9723ab3180ca199263745a24cd805f',
  bulles: [
    // Haut : Usages principaux
    { id: 1, text: 'Musculation variée', type: 'usage' },
    { id: 2, text: 'Renforcement à domicile', type: 'usage' },
    { id: 3, text: 'Convient à tous niveaux', type: 'usage' },
    // Droite : Sécurité et praticité
    { id: 4, text: 'Poids variable', type: 'carac' },
    { id: 5, text: 'Forme hexagonale (ne roule pas)', type: 'sécurité' },
    { id: 6, text: 'Revêtement anti-dérapant', type: 'sécurité' },
    { id: 7, text: 'Compact & facile à ranger', type: 'carac' },
    // Bas : Caractéristiques
    { id: 8, text: 'Matériaux robustes', type: 'carac' },
    { id: 9, text: 'Bonne répartition du poids', type: 'carac' },
    // Gauche : Confort & exemples
    { id: 10, text: 'Prise ergonomique', type: 'confort' },
    { id: 11, text: 'Surface lisse', type: 'confort' },
    { id: 12, text: 'Facile à nettoyer', type: 'confort' },
    { id: 13, text: 'Développé couché', type: 'usage' },
    { id: 14, text: 'Fentes', type: 'usage' },
    { id: 15, text: 'Rowing', type: 'usage' },
  ] as InfoMachine[],
}

const showBulles = ref<boolean[]>(Array(machine.bulles.length).fill(false))

onMounted(() => {
  machine.bulles.forEach((_, i) => {
    setTimeout(() => showBulles.value[i] = true, 200 + i * 120)
  })
})

function getBullePos(i: number) {
  const n = machine.bulles.length
  const angle = (2 * Math.PI * i) / n - Math.PI / 2
  const r = 300
  const x = 400 + r * Math.cos(angle)
  const y = 400 + r * Math.sin(angle)
  return { x, y }
}

import type { CSSProperties } from 'vue'
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
