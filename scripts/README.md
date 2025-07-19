# 🛠️ Scripts d'Exploitation MuscuScope

Ce dossier contient tous les scripts opérationnels pour déployer, maintenir et monitorer l'application MuscuScope en production.

## 📁 Structure

```
scripts/
├── scripts-index.sh           # Index et documentation des scripts
├── deployment/                # Scripts de déploiement
│   ├── check-prerequisites.sh # Vérification prérequis
│   ├── deploy-production.sh   # Déploiement production
│   └── rollback-production.sh # Rollback d'urgence
├── maintenance/               # Scripts de maintenance
│   ├── weekly-maintenance.sh  # Maintenance hebdomadaire
│   └── backup-production.sh   # Backup complet
├── monitoring/                # Scripts de monitoring
│   └── diagnose-health.sh     # Diagnostic santé système
├── testing/                   # Scripts de tests
│   ├── run-smoke-tests.sh     # Tests de fumée
│   └── load-testing.sh        # Tests de charge
└── incident/                  # Gestion d'incidents
    └── runbook-p0-app-down.sh # Incident critique P0
```

## 🚀 Démarrage Rapide

### 1. Rendre les scripts exécutables

```bash
chmod +x scripts/**/*.sh
```

### 2. Afficher l'aide complète

```bash
./scripts/scripts-index.sh
```

### 3. Configurer les variables d'environnement

```bash
# Configuration minimale
export GCP_PROJECT="muscuscope-prod"
export GCP_REGION="europe-west1"
export API_URL="https://api.muscuscope.com"
export FRONTEND_URL="https://muscuscope.com"
```

### 4. Vérifier les prérequis

```bash
./scripts/deployment/check-prerequisites.sh
```

### 5. Exécuter un diagnostic

```bash
./scripts/monitoring/diagnose-health.sh
```

## 🔄 Workflows Principaux

### Déploiement Production

```bash
# 1. Vérifications
./scripts/deployment/check-prerequisites.sh

# 2. Tests rapides
./scripts/testing/run-smoke-tests.sh --quick

# 3. Déploiement
./scripts/deployment/deploy-production.sh v1.2.3

# 4. Validation post-déploiement
./scripts/testing/run-smoke-tests.sh
```

### Maintenance Hebdomadaire

```bash
# Backup + maintenance + diagnostic
./scripts/maintenance/backup-production.sh
./scripts/maintenance/weekly-maintenance.sh
./scripts/monitoring/diagnose-health.sh
```

### Gestion d'Incident P0

```bash
# Procédure d'urgence
./scripts/incident/runbook-p0-app-down.sh

# Si rollback nécessaire
./scripts/deployment/rollback-production.sh
```

## ⚙️ Configuration

### Variables d'Environnement Requises

| Variable | Description | Exemple |
|----------|-------------|---------|
| `GCP_PROJECT` | Projet Google Cloud | `muscuscope-prod` |
| `GCP_REGION` | Région de déploiement | `europe-west1` |
| `API_URL` | URL de l'API backend | `https://api.muscuscope.com` |
| `FRONTEND_URL` | URL du frontend | `https://muscuscope.com` |
| `DOCKER_REGISTRY` | Registry Docker | `giovanni2002ynov` |
| `BACKUP_BUCKET` | Bucket de sauvegarde | `gs://muscuscope-backups` |

### Variables Optionnelles

| Variable | Description | Usage |
|----------|-------------|-------|
| `DATABASE_URL` | URL base de données | Tests et maintenance |
| `SLACK_WEBHOOK_EMERGENCY` | Webhook Slack urgence | Notifications incidents |
| `EMERGENCY_EMAIL` | Email d'urgence | Notifications critiques |

## 🔧 Outils Requis

### Obligatoires

- **gcloud CLI** (Google Cloud SDK)
- **docker** (Docker Engine)
- **terraform** (Infrastructure as Code)
- **curl** (Tests HTTP)
- **jq** (Parsing JSON)

### Optionnels

- **k6** (Tests de charge)
- **lighthouse** (Tests performance)
- **pgbench** (Tests base de données)

### Installation Rapide (Ubuntu/Debian)

```bash
# Outils de base
sudo apt-get update
sudo apt-get install -y curl jq bc

# Google Cloud SDK
curl https://sdk.cloud.google.com | bash
exec -l $SHELL

# Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Terraform
wget -O- https://apt.releases.hashicorp.com/gpg | sudo gpg --dearmor -o /usr/share/keyrings/hashicorp-archive-keyring.gpg
echo "deb [signed-by=/usr/share/keyrings/hashicorp-archive-keyring.gpg] https://apt.releases.hashicorp.com $(lsb_release -cs) main" | sudo tee /etc/apt/sources.list.d/hashicorp.list
sudo apt update && sudo apt install terraform

# K6 (tests de charge)
sudo gpg -k
sudo gpg --no-default-keyring --keyring /usr/share/keyrings/k6-archive-keyring.gpg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys C5AD17C747E3415A3642D57D77C6C491D6AC1D69
echo "deb [signed-by=/usr/share/keyrings/k6-archive-keyring.gpg] https://dl.k6.io/deb stable main" | sudo tee /etc/apt/sources.list.d/k6.list
sudo apt-get update
sudo apt-get install k6
```

## 📅 Planification Automatisée

### Crontab Recommandé

```bash
# Éditer crontab
crontab -e

# Ajouter ces tâches
# Backup quotidien à 2h
0 2 * * * /path/to/scripts/maintenance/backup-production.sh

# Maintenance hebdomadaire dimanche à 3h
0 3 * * 0 /path/to/scripts/maintenance/weekly-maintenance.sh

# Health check quotidien à 6h
0 6 * * * /path/to/scripts/monitoring/diagnose-health.sh

# Tests de fumée toutes les 4h
0 */4 * * * /path/to/scripts/testing/run-smoke-tests.sh --quick
```

## 🔒 Sécurité

### Authentification GCP

```bash
# Authentification interactive
gcloud auth login

# Ou avec compte de service (recommandé pour automatisation)
gcloud auth activate-service-account --key-file=path/to/key.json
```

### Permissions Minimales Requises

- **Cloud Run Admin** : Déploiement services
- **Cloud SQL Admin** : Gestion base de données
- **Storage Admin** : Gestion buckets
- **Logging Viewer** : Consultation logs
- **Monitoring Viewer** : Consultation métriques

## 🚨 Gestion d'Incidents

### Classification des Incidents

| Priorité | Description | Temps de Réponse | Script |
|----------|-------------|------------------|--------|
| **P0** | Service complètement down | 15 minutes | `runbook-p0-app-down.sh` |
| **P1** | Fonctionnalité critique KO | 1 heure | Diagnostic manuel |
| **P2** | Dégradation performance | 4 heures | `diagnose-health.sh` |
| **P3** | Problème mineur | 1 jour | Maintenance standard |

### Contacts d'Urgence

- **Tech Lead** : giovanni@muscuscope.com
- **DevOps** : devops@muscuscope.com  
- **Astreinte 24/7** : +33 6 XX XX XX XX

## 📊 Monitoring

### Dashboards Principaux

- **Grafana** : https://grafana.muscuscope.com
- **Google Cloud Console** : Monitoring et Logging
- **Status Page** : https://status.muscuscope.com (si configuré)

### Métriques Clés

- **Response Time** : API < 500ms (P95)
- **Error Rate** : < 1% erreurs 5xx
- **Uptime** : > 99.9%
- **Database** : Connexions < 80% pool

## 🔧 Dépannage

### Problèmes Fréquents

#### Script non exécutable

```bash
chmod +x ./scripts/**/*.sh
```

#### Authentification GCP échoue

```bash
gcloud auth list
gcloud auth login
```

#### Variables d'environnement manquantes

```bash
# Vérifier
env | grep -E '(GCP_|API_|DATABASE_)'

# Configurer
export GCP_PROJECT="muscuscope-prod"
# ... autres variables
```

#### Tests de charge échouent

```bash
# Installer K6
sudo apt-get install k6
# ou
brew install k6
```

#### Backup échoue

```bash
# Vérifier permissions bucket
gsutil ls gs://muscuscope-backups
gsutil iam get gs://muscuscope-backups
```

## 📝 Logs et Traces

### Emplacements des Logs

- **Scripts** : `/tmp/` (logs temporaires)
- **Application** : Google Cloud Logging
- **Système** : `/var/log/` (si local)

### Consultation Logs

```bash
# Logs application récents
gcloud logging read "resource.type=cloud_run_revision" --limit=100

# Logs erreurs
gcloud logging read "severity>=ERROR" --limit=50

# Logs d'un script spécifique
tail -f /tmp/script-execution-*.log
```

## 🆕 Mise à Jour des Scripts

### Ajout d'un Nouveau Script

1. Créer le script dans le dossier approprié
2. Rendre exécutable : `chmod +x nouveau-script.sh`
3. Ajouter la documentation dans `scripts-index.sh`
4. Tester en environnement staging
5. Mettre à jour ce README

### Convention de Nommage

- **Déploiement** : `deploy-`, `rollback-`, `check-`
- **Maintenance** : `maintenance-`, `backup-`, `cleanup-`
- **Monitoring** : `diagnose-`, `monitor-`, `health-`
- **Tests** : `test-`, `load-`, `smoke-`
- **Incidents** : `runbook-`, `incident-`, `emergency-`

## 📚 Documentation Complémentaire

- **Architecture** : `../cloud/README_C4_ARCHITECTURE.md`
- **Déploiement** : `../DOCUMENTATION_EXPLOITATION.md`
- **Tests** : `../STRATEGIE_TESTS.md`
- **Incidents** : `../PLAN_CORRECTION_BOGUES.md`

---

**🛠️ Scripts MuscuScope - Production Ready**

Pour une aide complète : `./scripts/scripts-index.sh`
