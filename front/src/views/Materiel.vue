<template>
  <v-app>
    <v-container class="d-flex flex-column align-center justify-center" style="min-height: 90vh;">
      <v-card class="pa-6" elevation="3" max-width="1000">
        <v-card-title class="headline text-center mb-4">{{ machine.nom }}</v-card-title>
        <div class="machine-cercle">
          <!-- SVG pour les traits entre l'image et les bulles -->
          <svg class="bulle-lines" width="320" height="320" viewBox="0 0 320 320" style="position:absolute;top:0;left:0;pointer-events:none;z-index:0;">
            <line v-for="(bulle, i) in machine.bulles" :key="'line-'+bulle.id"
              :x1="160" :y1="160"
              :x2="getBullePos(i).x" :y2="getBullePos(i).y"
              stroke="#1976d2" stroke-width="2" stroke-linecap="round" />
          </svg>
          <v-avatar size="120" class="machine-img">
            <v-img :src="machine.image" :alt="machine.nom" width="100%" height="100%" cover />
          </v-avatar>
          <div v-for="(bulle, i) in machine.bulles" :key="bulle.id" :class="['bulle', 'bulle-' + i, bulle.type]">
            <span>{{ bulle.text }}</span>
          </div>
        </div>
      </v-card>
    </v-container>
  </v-app>
</template>

<script setup lang="ts">
export interface InfoMachine {
  id: number;
  text: string;
  type: string;
}

const machine = {
  nom: 'Haltère hexagonale',
  image: 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fimages.ctfassets.net%2Fipjoepkmtnha%2F5fQCLXzP2H5BG5VcODED43%2Fc3d521f38437d3181c1705bb9979568f%2Fhex-dumbbell_hero&f=1&nofb=1&ipt=15dc359e6340d1aa00a421d911774013ca9723ab3180ca199263745a24cd805f',
  bulles: [
    { id: 1, text: 'Musculation', type: 'usage' },
    { id: 2, text: 'Poids variable', type: 'carac' },
    { id: 3, text: 'Prise ergonomique', type: 'confort' },
    { id: 4, text: 'Revêtement anti-dérapant', type: 'sécurité' },
    { id: 5, text: 'Polyvalent', type: 'usage' },
    { id: 6, text: 'Compact', type: 'carac' },
    { id: 7, text: 'Exemple', type: 'autre' },
  ] as InfoMachine[],
}

// Fonction utilitaire pour calculer la position des bulles (pour SVG)
function getBullePos(i: number) {
  const n = machine.bulles.length
  const angle = (2 * Math.PI * i) / n - Math.PI / 2
  const r = 130 // rayon du cercle des bulles
  const x = 160 + r * Math.cos(angle)
  const y = 160 + r * Math.sin(angle)
  return { x, y }
}
</script>

<style scoped>
.machine-cercle {
  position: relative;
  width: 320px;
  height: 320px;
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
  position: absolute;
  z-index: 3;
  width: 110px;
  height: 60px;
  background: rgba(227,242,253,0.95);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 1.08em;
  color: #15406a;
  box-shadow: 0 2px 8px #0001;
  text-align: center;
  padding: 0 10px;
  letter-spacing: 0.01em;
  text-shadow: 0 1px 2px #fff, 0 0 2px #1976d2;
}
.bulle-0 { left: 50%; top: 0%;    transform: translate(-50%, 0); }
.bulle-1 { right: 0%; top: 25%;   transform: translate(0, 0); }
.bulle-2 { right: 10%; bottom: 10%; transform: translate(0, 0); }
.bulle-3 { left: 50%; bottom: 0%;  transform: translate(-50%, 0); }
.bulle-4 { left: 0%; bottom: 25%;  transform: translate(0, 0); }
.bulle-5 { left: 10%; top: 10%;    transform: translate(0, 0); }

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

@media (max-width: 500px) {
  .machine-cercle {
    width: 220px;
    height: 220px;
  }
  .bulle {
    width: 80px;
    height: 40px;
    font-size: 0.9em;
  }
  .machine-img {
    width: 64px !important;
    height: 64px !important;
  }
}
</style>
