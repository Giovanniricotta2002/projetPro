#!/bin/bash
# scripts-index.sh - Index et utilisation des scripts MuscuScope

# Couleurs pour l'affichage
BLUE='\033[0;34m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

print_header() {
    echo -e "${CYAN}${BOLD}$1${NC}"
    echo -e "${CYAN}$(printf '=%.0s' {1..60})${NC}"
}

print_script() {
    local category="$1"
    local script="$2"
    local description="$3"
    local usage="$4"
    
    echo -e "${GREEN}📄 $script${NC}"
    echo -e "   ${YELLOW}Description:${NC} $description"
    if [[ -n "$usage" ]]; then
        echo -e "   ${BLUE}Usage:${NC} $usage"
    fi
    echo
}

echo -e "${BOLD}🛠️ Scripts d'Exploitation MuscuScope${NC}"
echo -e "${CYAN}Collection complète des scripts pour déploiement, maintenance et monitoring${NC}"
echo
echo -e "${YELLOW}📅 Dernière mise à jour: $(date)${NC}"
echo

# Déploiement
print_header "🚀 DÉPLOIEMENT"

print_script "deployment" "check-prerequisites.sh" \
    "Vérification des prérequis avant déploiement" \
    "./scripts/deployment/check-prerequisites.sh"

print_script "deployment" "deploy-production.sh" \
    "Déploiement complet en production avec tests" \
    "./scripts/deployment/deploy-production.sh [VERSION] [DRY_RUN=false]"

print_script "deployment" "rollback-production.sh" \
    "Rollback d'urgence vers version précédente" \
    "./scripts/deployment/rollback-production.sh [VERSION] [ENVIRONMENT] [FORCE]"

# Maintenance
print_header "🔧 MAINTENANCE"

print_script "maintenance" "weekly-maintenance.sh" \
    "Maintenance hebdomadaire automatisée" \
    "./scripts/maintenance/weekly-maintenance.sh"

print_script "maintenance" "backup-production.sh" \
    "Backup complet avec vérification d'intégrité" \
    "./scripts/maintenance/backup-production.sh"

# Monitoring
print_header "📊 MONITORING"

print_script "monitoring" "diagnose-health.sh" \
    "Diagnostic complet de santé du système" \
    "./scripts/monitoring/diagnose-health.sh"

# Tests
print_header "🧪 TESTS"

print_script "testing" "run-smoke-tests.sh" \
    "Tests de fumée pour validation rapide" \
    "./scripts/testing/run-smoke-tests.sh [--quick]"

print_script "testing" "load-testing.sh" \
    "Tests de charge avec K6 et Lighthouse" \
    "./scripts/testing/load-testing.sh"

# Incidents
print_header "🚨 GESTION D'INCIDENTS"

print_script "incident" "runbook-p0-app-down.sh" \
    "Procédure d'urgence application indisponible" \
    "./scripts/incident/runbook-p0-app-down.sh"

# Variables d'environnement
print_header "⚙️ VARIABLES D'ENVIRONNEMENT"

echo -e "${GREEN}Variables communes à configurer:${NC}"
echo
echo -e "${YELLOW}# Configuration GCP${NC}"
echo -e "export GCP_PROJECT=${GCP_PROJECT:-\"muscuscope-prod\"}"
echo -e "export GCP_REGION=${GCP_REGION:-\"europe-west1\"}"
echo
echo -e "${YELLOW}# URLs des services${NC}"
echo -e "export API_URL=${API_URL:-\"https://api.muscuscope.com\"}"
echo -e "export FRONTEND_URL=${FRONTEND_URL:-\"https://muscuscope.com\"}"
echo
echo -e "${YELLOW}# Stockage${NC}"
echo -e "export BACKUP_BUCKET=${BACKUP_BUCKET:-\"gs://muscuscope-backups\"}"
echo -e "export STORAGE_BUCKET=${STORAGE_BUCKET:-\"gs://muscuscope-assets\"}"
echo
echo -e "${YELLOW}# Base de données${NC}"
echo -e "export DB_INSTANCE=${DB_INSTANCE:-\"muscuscope-db-prod\"}"
echo -e "export DATABASE_URL=\"postgresql://user:pass@host:5432/db\""
echo
echo -e "${YELLOW}# Docker Registry${NC}"
echo -e "export DOCKER_REGISTRY=${DOCKER_REGISTRY:-\"giovanni2002ynov\"}"
echo
echo -e "${YELLOW}# Notifications (optionnelles)${NC}"
echo -e "export SLACK_WEBHOOK_EMERGENCY=\"https://hooks.slack.com/...\""
echo -e "export EMERGENCY_EMAIL=\"admin@muscuscope.com\""
echo

# Workflows recommandés
print_header "🔄 WORKFLOWS RECOMMANDÉS"

echo -e "${GREEN}🚀 Déploiement Production${NC}"
echo -e "1. ${BLUE}./scripts/deployment/check-prerequisites.sh${NC}"
echo -e "2. ${BLUE}./scripts/testing/run-smoke-tests.sh --quick${NC}"
echo -e "3. ${BLUE}./scripts/deployment/deploy-production.sh v1.2.3${NC}"
echo -e "4. ${BLUE}./scripts/testing/run-smoke-tests.sh${NC}"
echo

echo -e "${GREEN}🔧 Maintenance Hebdomadaire${NC}"
echo -e "1. ${BLUE}./scripts/maintenance/backup-production.sh${NC}"
echo -e "2. ${BLUE}./scripts/maintenance/weekly-maintenance.sh${NC}"
echo -e "3. ${BLUE}./scripts/monitoring/diagnose-health.sh${NC}"
echo

echo -e "${GREEN}🚨 Incident P0${NC}"
echo -e "1. ${BLUE}./scripts/incident/runbook-p0-app-down.sh${NC}"
echo -e "2. ${BLUE}./scripts/monitoring/diagnose-health.sh${NC}"
echo -e "3. ${BLUE}./scripts/deployment/rollback-production.sh${NC} (si nécessaire)"
echo

echo -e "${GREEN}🧪 Tests de Performance${NC}"
echo -e "1. ${BLUE}./scripts/testing/run-smoke-tests.sh${NC}"
echo -e "2. ${BLUE}./scripts/testing/load-testing.sh${NC}"
echo -e "3. ${BLUE}./scripts/monitoring/diagnose-health.sh${NC}"
echo

# Planification recommandée
print_header "📅 PLANIFICATION RECOMMANDÉE"

echo -e "${GREEN}⏰ Tâches automatisées (cron)${NC}"
echo
echo -e "${YELLOW}# Backup quotidien (2h du matin)${NC}"
echo -e "0 2 * * * /path/to/scripts/maintenance/backup-production.sh"
echo
echo -e "${YELLOW}# Maintenance hebdomadaire (dimanche 3h)${NC}"
echo -e "0 3 * * 0 /path/to/scripts/maintenance/weekly-maintenance.sh"
echo
echo -e "${YELLOW}# Health check quotidien (6h du matin)${NC}"
echo -e "0 6 * * * /path/to/scripts/monitoring/diagnose-health.sh"
echo
echo -e "${YELLOW}# Tests de fumée toutes les 4h${NC}"
echo -e "0 */4 * * * /path/to/scripts/testing/run-smoke-tests.sh --quick"
echo

# Permissions et sécurité
print_header "🔒 PERMISSIONS ET SÉCURITÉ"

echo -e "${GREEN}🔑 Permissions requises:${NC}"
echo
echo -e "• ${YELLOW}Google Cloud:${NC} Editor ou roles spécialisés (Cloud Run Admin, Cloud SQL Admin)"
echo -e "• ${YELLOW}Docker Hub:${NC} Push access au registry giovanni2002ynov"
echo -e "• ${YELLOW}Système:${NC} Exécution scripts, écriture /tmp/"
echo
echo -e "${GREEN}🛡️ Bonnes pratiques:${NC}"
echo
echo -e "• Utiliser des comptes de service pour l'automatisation"
echo -e "• Stocker les secrets dans Google Secret Manager"
echo -e "• Logs centralisés dans Cloud Logging"
echo -e "• Tests en staging avant production"
echo -e "• Rollback préparé pour chaque déploiement"
echo

# Dépannage
print_header "🔧 DÉPANNAGE"

echo -e "${GREEN}❓ Problèmes courants:${NC}"
echo
echo -e "${YELLOW}Authentification GCP échoue:${NC}"
echo -e "• Vérifier: ${BLUE}gcloud auth list${NC}"
echo -e "• Solution: ${BLUE}gcloud auth login${NC}"
echo
echo -e "${YELLOW}Script non exécutable:${NC}"
echo -e "• Solution: ${BLUE}chmod +x ./scripts/**/*.sh${NC}"
echo
echo -e "${YELLOW}Variables d'environnement manquantes:${NC}"
echo -e "• Vérifier: ${BLUE}env | grep -E '(GCP_|API_|DATABASE_)'${NC}"
echo -e "• Solution: Configurer les variables ci-dessus"
echo
echo -e "${YELLOW}Tests de charge échouent:${NC}"
echo -e "• Installer K6: ${BLUE}https://k6.io/docs/getting-started/installation/${NC}"
echo -e "• Installer jq: ${BLUE}sudo apt-get install jq${NC} (Linux)"
echo

# Contacts et support
print_header "📞 SUPPORT"

echo -e "${GREEN}🆘 Contacts d'urgence:${NC}"
echo
echo -e "• ${YELLOW}Tech Lead:${NC} giovanni@muscuscope.com"
echo -e "• ${YELLOW}DevOps:${NC} devops@muscuscope.com"
echo -e "• ${YELLOW}Astreinte:${NC} +33 6 XX XX XX XX"
echo
echo -e "${GREEN}📚 Documentation:${NC}"
echo
echo -e "• ${BLUE}Architecture:${NC} ./cloud/README_C4_ARCHITECTURE.md"
echo -e "• ${BLUE}API Docs:${NC} https://api.muscuscope.com/doc"
echo -e "• ${BLUE}Monitoring:${NC} https://grafana.muscuscope.com"
echo -e "• ${BLUE}Logs:${NC} Google Cloud Console > Logging"
echo

# Footer
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BOLD}🛠️ Scripts MuscuScope - Prêts pour la production${NC}"
echo -e "${CYAN}Utilisation: ./scripts/scripts-index.sh pour afficher cette aide${NC}"
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
