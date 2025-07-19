# 🚀 GitHub Actions Workflows - MuscuScope

Ce dossier contient les pipelines CI/CD automatisés pour la gestion des corrections d'anomalies dans MuscuScope, conformément au plan de correction des bogues.

## 📋 Vue d'Ensemble des Workflows

### 🚨 **hotfix.yml** - Pipeline Hotfix Critique

**Déclenché par**: Push sur branches `hotfix/*`
**Objectif**: Correction rapide des anomalies critiques avec déploiement automatique

#### Étapes du Pipeline:

1. **🧪 Tests Express** (5 min max)
   - Tests critiques uniquement
   - Analyse statique (PHPStan)
   - Vérification code style

2. **🐳 Build Images Docker**
   - Backend et Frontend
   - Push vers GitHub Container Registry

3. **🚀 Déploiement Staging**
   - Déploiement automatique sur Cloud Run
   - Tests smoke pour validation

4. **🏭 Déploiement Production** (si branche `hotfix/critical`)
   - Blue/Green deployment
   - Migration progressive du trafic (10% → 100%)
   - Monitoring post-déploiement

5. **↩️ Rollback Automatique**
   - En cas d'échec du monitoring
   - Retour version précédente
   - Notifications d'urgence

### 🔧 **bug-fix.yml** - Pipeline Correction Standard

**Déclenché par**: Push sur branches `fix/*`, `bugfix/*` et Pull Requests
**Objectif**: Workflow complet pour les corrections non-critiques

#### Étapes du Pipeline:

1. **📊 Analyse Qualité**
   - Tests unitaires avec couverture
   - Analyse statique complète
   - Upload vers Codecov

2. **🎨 Qualité Frontend**
   - ESLint et Prettier
   - Tests unitaires
   - Analyse bundle size

3. **🧪 Tests d'Intégration**
   - Base PostgreSQL
   - Migrations et fixtures
   - Tests Behat API

4. **🎭 Tests End-to-End**
   - Playwright multi-navigateurs
   - Environnement dockerisé

5. **🔒 Audit Sécurité**
   - Composer audit
   - NPM audit
   - CodeQL analysis

6. **⚡ Tests Performance**
   - K6 load testing
   - Lighthouse CI

7. **🔍 Environnement Preview**
   - Déploiement par PR
   - Tests de régression
   - Tests d'accessibilité

### 🔄 **monitoring-rollback.yml** - Monitoring Post-Déploiement

**Déclenché par**: Fin de workflow hotfix, planification, ou manuellement
**Objectif**: Surveillance continue et rollback automatique

#### Fonctionnalités:

1. **📊 Monitoring Continu** (15 min)
   - Health checks toutes les 30s
   - Seuil d'erreur configurable (5%)
   - Métriques de performance

2. **⚡ Tests Performance**
   - K6 load testing léger
   - Lighthouse performance

3. **🚨 Surveillance Taux d'Erreur**
   - Analyse logs automatique
   - Détection anomalies

4. **🔄 Rollback Automatique**
   - Déclenché si seuils dépassés
   - Notifications d'urgence

5. **📋 Analyse Logs**
   - Patterns critiques
   - Métriques système

## 🔧 Configuration Requise

### Secrets GitHub à Configurer:

```bash
# Google Cloud Platform
GCP_SA_KEY                 # Service Account JSON
GCP_PROJECT_ID            # ID du projet GCP

# Notifications
SLACK_WEBHOOK             # Webhook Slack standard
SLACK_WEBHOOK_CRITICAL    # Webhook Slack urgences

# Monitoring
CODECOV_TOKEN            # Token Codecov (optionnel)
```

### Variables d'Environnement:

```yaml
# Registre Docker
REGISTRY: ghcr.io
IMAGE_NAME: ${{ github.repository }}

# Monitoring
MONITORING_DURATION_MINUTES: 15
HEALTH_CHECK_INTERVAL_SECONDS: 30
ERROR_THRESHOLD_PERCENT: 5
```

## 🎯 Utilisation selon les Types d'Anomalies

### 🔴 **CRITIQUE** - SLA 4h

```bash
# 1. Créer branche hotfix
git checkout -b hotfix/critical-bug-description

# 2. Correction rapide + tests
# Développement de la correction...

# 3. Push déclenche pipeline automatique
git push origin hotfix/critical-bug-description

# 4. Pipeline s'exécute automatiquement:
#    ✅ Tests express (5 min)
#    ✅ Build + Deploy staging
#    ✅ Deploy production automatique
#    ✅ Monitoring 15 min
#    ✅ Rollback si problème
```

### 🟠 **MAJEUR** - SLA 24h

```bash
# 1. Créer branche fix
git checkout -b fix/major-bug-description

# 2. Développement + tests complets
# Développement standard...

# 3. Créer Pull Request
# Pipeline complet s'exécute:
#    ✅ Tests complets
#    ✅ Analyse sécurité
#    ✅ Tests performance
#    ✅ Environnement preview
#    ✅ Review obligatoire

# 4. Merge vers main → déploiement
```

### 🟡 **MINEUR** - SLA 72h

```bash
# Même processus que MAJEUR
# Intégré dans sprint planning normal
```

## 📊 Métriques et Monitoring

### Indicateurs Surveillés:

- **Disponibilité**: > 99.9%
- **Temps de réponse P95**: < 1000ms
- **Taux d'erreur**: < 0.1%
- **CPU/Mémoire**: Seuils configurables
- **Throughput**: req/min

### Rapports Générés:

- **Monitoring Report**: Après chaque déploiement
- **Performance Report**: Tests de charge
- **Security Report**: Audit sécurité
- **Coverage Report**: Couverture tests

## 🚀 Workflows Personnalisés

### Déclenchement Manuel:

```yaml
# Monitoring spécifique
workflow_dispatch:
  inputs:
    action: [monitor, rollback, health-check]
    environment: [production, staging]
```

### Hooks Automatiques:

- **Post-déploiement**: Monitoring automatique
- **Échec critique**: Rollback + notifications
- **PR ouverte**: Tests complets + preview

## 🔧 Maintenance des Pipelines

### Mise à Jour Recommandée:

1. **Actions versions**: Maintenir à jour
2. **Seuils monitoring**: Ajuster selon métriques
3. **Tests critical**: Identifier nouveaux tests
4. **Notifications**: Adapter aux équipes

### Debugging:

- **Logs détaillés**: Chaque étape documentée
- **Artifacts**: Rapports sauvegardés
- **Timeout appropriés**: Éviter blocages

---

**🎯 Ces pipelines garantissent une correction rapide et sûre des anomalies, avec surveillance automatique et rollback en cas de problème, conformément au plan de correction des bogues de MuscuScope.**
