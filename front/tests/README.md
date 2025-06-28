# Tests Frontend - Vue.js + Vitest

Ce document décrit la suite de tests unitaires pour le frontend Vue.js de l'application.

## Configuration des tests

### Frameworks et outils utilisés

- **Vitest** : Framework de test rapide et moderne pour Vite
- **Vue Test Utils** : Utilitaires officiels pour tester les composants Vue
- **Vuetify** : Support complet pour les composants Vuetify
- **Pinia** : Tests des stores de gestion d'état
- **Vue Router** : Tests de navigation et routing

### Structure des tests

```
tests/
├── spec/
│   ├── components/         # Tests des composants Vue
│   ├── views/             # Tests des vues/pages
│   ├── stores/            # Tests des stores Pinia
│   └── config.spec.ts     # Tests de configuration
├── setup.ts               # Configuration globale des tests
└── vitest.d.ts           # Types TypeScript pour Vitest
```

## Scripts disponibles

```bash
# Lancer les tests en mode watch
npm run test

# Lancer les tests une seule fois
npm run test:run

# Lancer les tests avec couverture de code
npm run test:coverage
```

## Tests implémentés

### 🏪 Tests des Stores (Pinia)

#### useCSRFToken Store

- ✅ Initialisation avec état par défaut
- ✅ Récupération réussie du token CSRF
- ✅ Gestion des erreurs HTTP
- ✅ Gestion des erreurs réseau
- ✅ États de chargement
- ✅ Gestion des réponses vides/malformées

#### useRuleInput Store

- ✅ Validation des logins (longueur, format)
- ✅ Validation des mots de passe (longueur minimale)
- ✅ Validation des emails (format RFC)
- ✅ Validation des URLs (protocoles, domaines)
- ✅ Cas limites et caractères spéciaux

### 🎯 Tests des Composants

#### HelloWorld Component

- ✅ Rendu correct du composant
- ✅ Intégration Vuetify
- ✅ Réactivité Vue
- ✅ Cycle de vie (mount/unmount)

#### Login View (Page de connexion)

- ✅ Rendu du formulaire de connexion
- ✅ Validation des champs (login/password)
- ✅ Récupération automatique du token CSRF
- ✅ Soumission du formulaire avec succès
- ✅ Gestion des erreurs de connexion
- ✅ Redirection vers la page d'inscription
- ✅ États du bouton de soumission
- ✅ Inclusion du token CSRF dans les requêtes

### ⚙️ Tests de Configuration

#### Module config.ts

- ✅ URL d'API par défaut
- ✅ Variables d'environnement
- ✅ Headers CORS corrects
- ✅ Méthodes HTTP supportées
- ✅ Headers autorisés (CSRF, Content-Type)

## Mocks et utilitaires

### Mocks globaux (setup.ts)

- **ResizeObserver** : Polyfill pour Vuetify
- **fetch** : Mock des requêtes HTTP
- **localStorage/sessionStorage** : Mock du stockage navigateur
- **window.location** : Mock de l'objet location
- **console** : Mock des méthodes de log

### Utilitaires de test

- Configuration Vuetify complète
- Setup Pinia pour les stores
- Configuration Vue Router pour la navigation
- Helpers pour les assertions

## Couverture de code

La configuration inclut la génération de rapports de couverture :

```bash
npm run test:coverage
```

### Exclusions de couverture

- `node_modules/`
- `tests/` (fichiers de test)
- `**/*.d.ts` (fichiers de types)
- `vite.config.mts` (configuration)
- `src/main.ts` (point d'entrée)

### Rapports générés

- **Text** : Résumé dans le terminal
- **JSON** : Données brutes pour l'intégration CI/CD
- **HTML** : Rapport détaillé navigable

## Bonnes pratiques implémentées

### 🧪 Structure des tests

- **AAA Pattern** : Arrange, Act, Assert
- **Tests unitaires isolés** : Chaque test est indépendant
- **Mocks appropriés** : Services externes mockés
- **Descriptions claires** : Noms de tests explicites

### 🔧 Configuration

- **Setup global** : Configuration partagée
- **Types TypeScript** : Support complet des types
- **Environment jsdom** : Simulation du DOM navigateur
- **Hot reload** : Tests en mode watch pendant le développement

### 📊 Assertions

- **Tests positifs et négatifs** : Cas de succès et d'erreur
- **Cas limites** : Valeurs vides, nulles, extrêmes
- **États intermédiaires** : Loading, erreurs, succès
- **Intégration** : Tests des interactions entre composants

## Intégration CI/CD

Les tests sont prêts pour l'intégration dans un pipeline CI/CD :

```yaml
# Exemple GitHub Actions
- name: Run tests
  run: npm run test:run

- name: Generate coverage
  run: npm run test:coverage

- name: Upload coverage
  uses: codecov/codecov-action@v3
```

## Extensions possibles

### Tests d'intégration

- Tests end-to-end avec Playwright/Cypress
- Tests d'APIs avec MSW (Mock Service Worker)
- Tests de performance avec Lighthouse CI

### Tests visuels

- Regression testing avec Percy/Chromatic
- Tests d'accessibilité avec axe-core
- Tests de responsivité sur différents viewports

### Métriques avancées

- Code coverage différentiel
- Performance budgets
- Bundle size analysis

## Debugging

### Lancer un test spécifique

```bash
npm run test -- --run login.spec.ts
```

### Mode debug avec breakpoints

```bash
npm run test -- --inspect-brk
```

### Verbose output

```bash
npm run test -- --reporter=verbose
```

## Conclusion

Cette suite de tests fournit une couverture complète du frontend Vue.js avec :

- ✅ **Tests unitaires** complets pour stores et composants
- ✅ **Mocks appropriés** pour l'isolation des tests  
- ✅ **Configuration moderne** avec Vitest et Vue Test Utils
- ✅ **Couverture de code** avec rapports détaillés
- ✅ **Types TypeScript** pour la robustesse
- ✅ **Intégration CI/CD** prête

Les tests garantissent la qualité, la maintenabilité et la fiabilité de l'interface utilisateur.
