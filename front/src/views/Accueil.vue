<template>
  <v-app>
    <v-container class="py-10" style="text-align:center;">
      <v-img src="/src/assets/logo.png" alt="Logo musculation" max-width="120" class="mx-auto mb-6" />
      <h1 class="text-h3 font-weight-bold mb-4">Plateforme de compréhension des machines de musculation</h1>
      <h2 class="text-h6 font-italic mb-6" style="color:#388e3c;">Comprendre, progresser, partager</h2>
      <p class="mb-6">
        Bienvenue sur la plateforme dédiée à la compréhension des machines et équipements de musculation.<br>
        Ce site s’adresse à toute personne désirant mieux comprendre le fonctionnement des machines de musculation, du débutant à l'utilisateur confirmé.
      </p>
      <v-alert type="info" class="mb-8" border="start" color="primary" variant="tonal">
        <span class="font-weight-bold">Notre mission :</span> rendre la musculation accessible à tous grâce à l’information et l’entraide.
      </v-alert>
      <h2 class="text-h5 font-weight-bold mb-4">Fonctionnalités principales</h2>
      <v-list two-line>
        <v-list-item>
          <v-list-item-content>
            <v-list-item-title class="font-weight-bold">Fiches explicatives détaillées</v-list-item-title>
            <v-list-item-subtitle>Texte, photos, schémas sur chaque machine.</v-list-item-subtitle>
          </v-list-item-content>
        </v-list-item>
        <v-list-item>
          <v-list-item-content>
            <v-list-item-title class="font-weight-bold">Forum participatif</v-list-item-title>
            <v-list-item-subtitle>Les utilisateurs peuvent suggérer des modifications ou enrichir les descriptions.</v-list-item-subtitle>
          </v-list-item-content>
        </v-list-item>
        <v-list-item>
          <v-list-item-content>
            <v-list-item-title class="font-weight-bold">Base de données collaborative</v-list-item-title>
            <v-list-item-subtitle>Centralisation et vulgarisation des connaissances sur le matériel de musculation.</v-list-item-subtitle>
          </v-list-item-content>
        </v-list-item>
      </v-list>
      <blockquote class="mt-10" style="font-style:italic; color:#757575;">
        « La connaissance, c’est la force. »
      </blockquote>
    </v-container>
  </v-app>
</template>

<script setup lang="ts">
// Page d'accueil enrichie avec slogan, mission, image et citation
</script>

<style scoped>
h1 {
  color: #1976d2;
}
</style>
