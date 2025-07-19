#!/bin/bash
# diagnose-health.sh - Diagnostic de santé complet du système

set -e

# Configuration
GCP_PROJECT=${GCP_PROJECT:-"muscuscope-prod"}
GCP_REGION=${GCP_REGION:-"europe-west1"}
SERVICE_NAME="muscuscope"

# Couleurs pour les logs
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

log_info() {
    echo -e "${BLUE}ℹ️ $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️ $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

log_section() {
    echo -e "${CYAN}🔍 $1${NC}"
}

# Variables pour le scoring
HEALTH_SCORE=0
TOTAL_CHECKS=0
CRITICAL_ISSUES=0
WARNING_ISSUES=0

# Fonction pour enregistrer un test
check_test() {
    local test_name="$1"
    local test_result="$2"
    local is_critical="${3:-false}"
    
    ((TOTAL_CHECKS++))
    
    if [[ "$test_result" == "OK" ]]; then
        log_success "$test_name"
        ((HEALTH_SCORE++))
    elif [[ "$test_result" == "WARNING" ]]; then
        log_warning "$test_name"
        ((WARNING_ISSUES++))
        if [[ "$is_critical" == "true" ]]; then
            ((CRITICAL_ISSUES++))
        fi
    else
        log_error "$test_name"
        ((CRITICAL_ISSUES++))
    fi
}

echo "🏥 Diagnostic de Santé Complet - MuscuScope"
echo "📅 Date: $(date)"
echo "🏗️ Projet: $GCP_PROJECT"
echo "🌍 Région: $GCP_REGION"
echo ""

# Section 1: Infrastructure GCP
log_section "Section 1: Infrastructure Google Cloud Platform"

# Test 1.1: Authentification GCP
if gcloud auth list --filter=status:ACTIVE --format="value(account)" | grep -q "@"; then
    check_test "Authentification GCP active" "OK"
else
    check_test "Authentification GCP manquante" "ERROR" true
fi

# Test 1.2: APIs requises
REQUIRED_APIS=("run.googleapis.com" "sql.googleapis.com" "storage.googleapis.com" "logging.googleapis.com")
for api in "${REQUIRED_APIS[@]}"; do
    if gcloud services list --enabled --filter="name:$api" --format="value(name)" | grep -q "$api"; then
        check_test "API $api activée" "OK"
    else
        check_test "API $api non activée" "ERROR" true
    fi
done

# Test 1.3: Quotas GCP
CPU_QUOTA=$(gcloud compute project-info describe --format="value(quotas[?metric=='CPUS'].limit)" | head -1)
if [[ ${CPU_QUOTA:-0} -gt 100 ]]; then
    check_test "Quota CPU suffisant ($CPU_QUOTA vCPUs)" "OK"
else
    check_test "Quota CPU potentiellement insuffisant ($CPU_QUOTA vCPUs)" "WARNING"
fi

# Section 2: Services Cloud Run
log_section "Section 2: Services Cloud Run"

SERVICES=("$SERVICE_NAME-backend" "$SERVICE_NAME-frontend")

for service in "${SERVICES[@]}"; do
    # Test 2.1: Existence du service
    if gcloud run services describe "$service" --region="$GCP_REGION" >/dev/null 2>&1; then
        check_test "Service $service existe" "OK"
        
        # Test 2.2: Status du service
        STATUS=$(gcloud run services describe "$service" \
            --region="$GCP_REGION" \
            --format="value(status.conditions[0].status)")
        
        if [[ "$STATUS" == "True" ]]; then
            check_test "Service $service opérationnel" "OK"
        else
            check_test "Service $service en erreur (Status: $STATUS)" "ERROR" true
        fi
        
        # Test 2.3: URL accessible
        SERVICE_URL=$(gcloud run services describe "$service" \
            --region="$GCP_REGION" \
            --format="value(status.url)")
        
        if curl -f -s --max-time 10 "$SERVICE_URL" >/dev/null; then
            check_test "URL $service accessible" "OK"
        else
            check_test "URL $service inaccessible" "ERROR" true
        fi
        
        # Test 2.4: Métriques de performance
        MEMORY_LIMIT=$(gcloud run services describe "$service" \
            --region="$GCP_REGION" \
            --format="value(spec.template.spec.containers[0].resources.limits.memory)")
        
        if [[ "$MEMORY_LIMIT" =~ [0-9]+Mi$ ]] && [[ ${MEMORY_LIMIT%Mi} -ge 256 ]]; then
            check_test "Mémoire $service configurée ($MEMORY_LIMIT)" "OK"
        else
            check_test "Mémoire $service potentiellement insuffisante ($MEMORY_LIMIT)" "WARNING"
        fi
        
    else
        check_test "Service $service introuvable" "ERROR" true
    fi
done

# Section 3: Base de données
log_section "Section 3: Base de données PostgreSQL"

DB_INSTANCE="muscuscope-db-prod"

# Test 3.1: Instance de base de données
if gcloud sql instances describe "$DB_INSTANCE" >/dev/null 2>&1; then
    check_test "Instance DB $DB_INSTANCE existe" "OK"
    
    # Test 3.2: État de l'instance
    DB_STATE=$(gcloud sql instances describe "$DB_INSTANCE" --format="value(state)")
    if [[ "$DB_STATE" == "RUNNABLE" ]]; then
        check_test "Instance DB en cours d'exécution" "OK"
    else
        check_test "Instance DB en état anormal: $DB_STATE" "ERROR" true
    fi
    
    # Test 3.3: Version PostgreSQL
    DB_VERSION=$(gcloud sql instances describe "$DB_INSTANCE" --format="value(databaseVersion)")
    if [[ "$DB_VERSION" =~ POSTGRES_14 ]] || [[ "$DB_VERSION" =~ POSTGRES_15 ]]; then
        check_test "Version PostgreSQL supportée ($DB_VERSION)" "OK"
    else
        check_test "Version PostgreSQL obsolète ($DB_VERSION)" "WARNING"
    fi
    
    # Test 3.4: Backups automatiques
    BACKUP_ENABLED=$(gcloud sql instances describe "$DB_INSTANCE" --format="value(settings.backupConfiguration.enabled)")
    if [[ "$BACKUP_ENABLED" == "True" ]]; then
        check_test "Backups automatiques activés" "OK"
    else
        check_test "Backups automatiques désactivés" "ERROR" true
    fi
    
else
    check_test "Instance DB $DB_INSTANCE introuvable" "ERROR" true
fi

# Section 4: Storage
log_section "Section 4: Cloud Storage"

STORAGE_BUCKETS=("muscuscope-assets" "muscuscope-backups")

for bucket in "${STORAGE_BUCKETS[@]}"; do
    # Test 4.1: Existence du bucket
    if gsutil ls "gs://$bucket" >/dev/null 2>&1; then
        check_test "Bucket $bucket accessible" "OK"
        
        # Test 4.2: Permissions
        if gsutil iam get "gs://$bucket" >/dev/null 2>&1; then
            check_test "Permissions bucket $bucket OK" "OK"
        else
            check_test "Problème permissions bucket $bucket" "WARNING"
        fi
        
        # Test 4.3: Taille du bucket (pour assets)
        if [[ "$bucket" == "muscuscope-assets" ]]; then
            BUCKET_SIZE=$(gsutil du -s "gs://$bucket" | awk '{print $1}')
            if [[ $BUCKET_SIZE -gt 0 ]]; then
                READABLE_SIZE=$(numfmt --to=iec $BUCKET_SIZE)
                check_test "Bucket assets non vide ($READABLE_SIZE)" "OK"
            else
                check_test "Bucket assets vide" "WARNING"
            fi
        fi
        
    else
        check_test "Bucket $bucket inaccessible" "ERROR" true
    fi
done

# Section 5: Connectivité et endpoints
log_section "Section 5: Endpoints et connectivité"

# Test 5.1: Frontend
FRONTEND_URL="https://muscuscope.com"
if curl -f -s --max-time 15 "$FRONTEND_URL" >/dev/null; then
    check_test "Frontend principal accessible" "OK"
    
    # Test titre de page
    PAGE_TITLE=$(curl -s --max-time 10 "$FRONTEND_URL" | grep -o '<title>[^<]*</title>' | sed 's/<title>\|<\/title>//g')
    if [[ "$PAGE_TITLE" =~ MuscuScope ]]; then
        check_test "Titre de page correct" "OK"
    else
        check_test "Titre de page suspect: $PAGE_TITLE" "WARNING"
    fi
else
    check_test "Frontend principal inaccessible" "ERROR" true
fi

# Test 5.2: API Backend
API_URL="https://api.muscuscope.com"

# Health endpoint
if curl -f -s --max-time 10 "$API_URL/api/health" >/dev/null; then
    check_test "API health endpoint accessible" "OK"
    
    # Test réponse JSON
    HEALTH_RESPONSE=$(curl -s --max-time 10 "$API_URL/api/health")
    if echo "$HEALTH_RESPONSE" | jq . >/dev/null 2>&1; then
        check_test "API health retourne JSON valide" "OK"
    else
        check_test "API health réponse invalide" "WARNING"
    fi
else
    check_test "API health endpoint inaccessible" "ERROR" true
fi

# Test endpoints critiques
ENDPOINTS=("/api/health/db" "/api/doc" "/api/csrf-token")
for endpoint in "${ENDPOINTS[@]}"; do
    if curl -f -s --max-time 10 "$API_URL$endpoint" >/dev/null; then
        check_test "Endpoint $endpoint accessible" "OK"
    else
        check_test "Endpoint $endpoint inaccessible" "WARNING"
    fi
done

# Section 6: Sécurité
log_section "Section 6: Configuration de sécurité"

# Test 6.1: HTTPS obligatoire
for url in "$FRONTEND_URL" "$API_URL"; do
    HTTP_VERSION=$(echo "$url" | sed 's/https/http/')
    HTTP_RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" --max-time 10 "$HTTP_VERSION" || echo "000")
    
    if [[ "$HTTP_RESPONSE" == "301" ]] || [[ "$HTTP_RESPONSE" == "302" ]]; then
        check_test "Redirection HTTPS configurée pour $(basename "$url")" "OK"
    else
        check_test "Redirection HTTPS manquante pour $(basename "$url") (Code: $HTTP_RESPONSE)" "WARNING"
    fi
done

# Test 6.2: Headers de sécurité
SECURITY_HEADERS=("X-Content-Type-Options" "X-Frame-Options" "Strict-Transport-Security")
for header in "${SECURITY_HEADERS[@]}"; do
    if curl -s -I --max-time 10 "$FRONTEND_URL" | grep -qi "$header"; then
        check_test "Header sécurité $header présent" "OK"
    else
        check_test "Header sécurité $header manquant" "WARNING"
    fi
done

# Section 7: Performance
log_section "Section 7: Performance et métriques"

# Test 7.1: Temps de réponse
RESPONSE_TIME=$(curl -o /dev/null -s -w "%{time_total}" --max-time 10 "$API_URL/api/health" || echo "10.0")
RESPONSE_MS=$(echo "$RESPONSE_TIME * 1000" | bc | cut -d. -f1)

if [[ $RESPONSE_MS -lt 500 ]]; then
    check_test "Temps de réponse API excellent (${RESPONSE_MS}ms)" "OK"
elif [[ $RESPONSE_MS -lt 2000 ]]; then
    check_test "Temps de réponse API acceptable (${RESPONSE_MS}ms)" "OK"
else
    check_test "Temps de réponse API dégradé (${RESPONSE_MS}ms)" "WARNING"
fi

# Test 7.2: Disponibilité des métriques
if curl -f -s --max-time 10 "https://grafana.muscuscope.com" >/dev/null; then
    check_test "Dashboard Grafana accessible" "OK"
else
    check_test "Dashboard Grafana inaccessible" "WARNING"
fi

# Section 8: Logs et monitoring
log_section "Section 8: Logs et monitoring"

# Test 8.1: Logs récents
RECENT_LOGS=$(gcloud logging read "timestamp >= \"$(date -d '1 hour ago' -Iseconds)\" AND resource.type=cloud_run_revision" \
    --limit=10 --format="value(timestamp)" | wc -l)

if [[ $RECENT_LOGS -gt 0 ]]; then
    check_test "Logs récents présents ($RECENT_LOGS entrées)" "OK"
else
    check_test "Aucun log récent trouvé" "WARNING"
fi

# Test 8.2: Erreurs récentes
ERROR_LOGS=$(gcloud logging read "timestamp >= \"$(date -d '1 hour ago' -Iseconds)\" AND severity>=ERROR" \
    --limit=5 --format="value(timestamp)" | wc -l)

if [[ $ERROR_LOGS -eq 0 ]]; then
    check_test "Aucune erreur récente" "OK"
elif [[ $ERROR_LOGS -lt 5 ]]; then
    check_test "$ERROR_LOGS erreurs récentes (tolérable)" "WARNING"
else
    check_test "$ERROR_LOGS erreurs récentes (préoccupant)" "ERROR"
fi

# Section 9: Résumé et scoring
log_section "Section 9: Résumé et recommandations"

# Calcul du score de santé
HEALTH_PERCENTAGE=$((HEALTH_SCORE * 100 / TOTAL_CHECKS))

echo ""
echo "📊 RÉSUMÉ DU DIAGNOSTIC:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🎯 Score de santé global: $HEALTH_PERCENTAGE% ($HEALTH_SCORE/$TOTAL_CHECKS tests réussis)"
echo "🔴 Problèmes critiques: $CRITICAL_ISSUES"
echo "🟡 Avertissements: $WARNING_ISSUES"
echo "⏱️ Durée diagnostic: $SECONDS secondes"
echo ""

# Évaluation globale
if [[ $HEALTH_PERCENTAGE -ge 90 ]]; then
    log_success "🟢 EXCELLENT - Système en parfaite santé"
    OVERALL_STATUS="EXCELLENT"
elif [[ $HEALTH_PERCENTAGE -ge 75 ]]; then
    log_success "🟡 BON - Système opérationnel avec quelques améliorations possibles"
    OVERALL_STATUS="BON"
elif [[ $HEALTH_PERCENTAGE -ge 60 ]]; then
    log_warning "🟠 MOYEN - Attention requise sur certains composants"
    OVERALL_STATUS="MOYEN"
else
    log_error "🔴 CRITIQUE - Intervention immédiate requise"
    OVERALL_STATUS="CRITIQUE"
fi

echo ""
echo "🔍 RECOMMANDATIONS:"

if [[ $CRITICAL_ISSUES -gt 0 ]]; then
    echo "  🚨 URGENT: $CRITICAL_ISSUES problèmes critiques à résoudre immédiatement"
fi

if [[ $WARNING_ISSUES -gt 3 ]]; then
    echo "  ⚠️ MAINTENANCE: $WARNING_ISSUES avertissements nécessitent une attention"
fi

if [[ $HEALTH_PERCENTAGE -ge 85 ]]; then
    echo "  ✅ PRÉVENTIF: Continuer la surveillance et maintenance régulière"
fi

# Génération rapport JSON
REPORT_FILE="/tmp/health-report-$(date +%Y%m%d_%H%M%S).json"

cat > "$REPORT_FILE" <<EOF
{
  "timestamp": "$(date -Iseconds)",
  "project": "$GCP_PROJECT",
  "region": "$GCP_REGION",
  "diagnostic": {
    "overall_status": "$OVERALL_STATUS",
    "health_score": $HEALTH_PERCENTAGE,
    "tests_passed": $HEALTH_SCORE,
    "total_tests": $TOTAL_CHECKS,
    "critical_issues": $CRITICAL_ISSUES,
    "warnings": $WARNING_ISSUES,
    "duration_seconds": $SECONDS
  },
  "next_diagnostic": "$(date -d '24 hours' -Iseconds)"
}
EOF

log_info "Rapport JSON généré: $REPORT_FILE"

echo ""
if [[ $CRITICAL_ISSUES -eq 0 ]]; then
    echo "🎉 Diagnostic terminé - Système opérationnel!"
else
    echo "⚠️ Diagnostic terminé - Intervention requise!"
fi
