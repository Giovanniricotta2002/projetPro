#!/bin/bash
# runbook-p0-app-down.sh - Incident critique application down

set -e

# Configuration
GCP_PROJECT=${GCP_PROJECT:-"muscuscope-prod"}
GCP_REGION=${GCP_REGION:-"europe-west1"}
SERVICE_NAME="muscuscope"
EMERGENCY_BUCKET="gs://muscuscope-emergency"

# Couleurs pour les logs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
BOLD='\033[1m'
NC='\033[0m'

log_emergency() {
    echo -e "${RED}${BOLD}🚨 EMERGENCY: $1${NC}"
}

log_info() {
    echo -e "${BLUE}ℹ️ $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️ $1${NC}"
}

# Fonction de notification d'urgence
emergency_notify() {
    local message="$1"
    local details="$2"
    
    # Slack (si configuré)
    if [[ -n "$SLACK_WEBHOOK_EMERGENCY" ]]; then
        curl -X POST -H 'Content-type: application/json' \
            --data "{\"text\":\"🚨 $message\", \"attachments\":[{\"color\":\"danger\",\"text\":\"$details\"}]}" \
            "$SLACK_WEBHOOK_EMERGENCY" 2>/dev/null || true
    fi
    
    # Email (si configuré)
    if command -v mail &> /dev/null && [[ -n "$EMERGENCY_EMAIL" ]]; then
        echo "$details" | mail -s "🚨 P0 INCIDENT: $message" "$EMERGENCY_EMAIL" 2>/dev/null || true
    fi
    
    # Log système
    logger "MUSCUSCOPE P0 INCIDENT: $message - $details"
}

echo "🚨 INCIDENT P0: Application Down - $(date)"
echo "🏗️ Projet: $GCP_PROJECT"
echo "🌍 Région: $GCP_REGION"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Notification immédiate d'incident
INCIDENT_ID="P0-$(date +%Y%m%d_%H%M%S)"
emergency_notify "Application MuscuScope indisponible" "Incident ID: $INCIDENT_ID - Investigation en cours"

# Phase 1: Diagnostic rapide de l'infrastructure
log_emergency "Phase 1: Diagnostic rapide infrastructure"

# 1.1 Vérification Cloud Run services
log_info "Vérification état des services Cloud Run..."
BACKEND_STATUS=$(gcloud run services describe $SERVICE_NAME-backend \
    --region=$GCP_REGION \
    --format="value(status.conditions[0].status)" 2>/dev/null || echo "UNKNOWN")

FRONTEND_STATUS=$(gcloud run services describe $SERVICE_NAME-frontend \
    --region=$GCP_REGION \
    --format="value(status.conditions[0].status)" 2>/dev/null || echo "UNKNOWN")

echo "Backend Status: $BACKEND_STATUS"
echo "Frontend Status: $FRONTEND_STATUS"

# 1.2 Test de connectivité basique
log_info "Tests de connectivité basiques..."
BACKEND_URL=$(gcloud run services describe $SERVICE_NAME-backend \
    --region=$GCP_REGION \
    --format="value(status.url)" 2>/dev/null || echo "")

FRONTEND_URL=$(gcloud run services describe $SERVICE_NAME-frontend \
    --region=$GCP_REGION \
    --format="value(status.url)" 2>/dev/null || echo "")

if [[ -n "$BACKEND_URL" ]]; then
    BACKEND_HTTP_CODE=$(curl -o /dev/null -s -w "%{http_code}" --max-time 10 "$BACKEND_URL/api/health" || echo "000")
    echo "Backend HTTP Code: $BACKEND_HTTP_CODE"
else
    echo "Backend URL non récupérable"
    BACKEND_HTTP_CODE="000"
fi

if [[ -n "$FRONTEND_URL" ]]; then
    FRONTEND_HTTP_CODE=$(curl -o /dev/null -s -w "%{http_code}" --max-time 10 "$FRONTEND_URL" || echo "000")
    echo "Frontend HTTP Code: $FRONTEND_HTTP_CODE"
else
    echo "Frontend URL non récupérable"
    FRONTEND_HTTP_CODE="000"
fi

# 1.3 Vérification base de données
log_info "Vérification base de données..."
DB_STATUS=$(gcloud sql instances describe muscuscope-db-prod \
    --format="value(state)" 2>/dev/null || echo "UNKNOWN")
echo "Database Status: $DB_STATUS"

# Phase 2: Identification cause probable
log_emergency "Phase 2: Identification cause probable"

PROBABLE_CAUSE=""
CORRECTIVE_ACTION=""

if [[ "$BACKEND_STATUS" != "True" || "$BACKEND_HTTP_CODE" != "200" ]]; then
    PROBABLE_CAUSE="Backend service défaillant"
    if [[ "$DB_STATUS" != "RUNNABLE" ]]; then
        PROBABLE_CAUSE="$PROBABLE_CAUSE + Base de données inaccessible"
    fi
elif [[ "$FRONTEND_STATUS" != "True" || "$FRONTEND_HTTP_CODE" != "200" ]]; then
    PROBABLE_CAUSE="Frontend service défaillant"
else
    PROBABLE_CAUSE="Problème réseau ou Load Balancer"
fi

log_warning "Cause probable identifiée: $PROBABLE_CAUSE"

# Phase 3: Actions correctives immédiates
log_emergency "Phase 3: Actions correctives immédiates"

# 3.1 Vérification déploiement récent
log_info "Vérification d'un déploiement récent..."
LAST_BACKEND_DEPLOY=$(gcloud run revisions list \
    --service=$SERVICE_NAME-backend \
    --region=$GCP_REGION \
    --limit=1 \
    --format="value(metadata.creationTimestamp)" 2>/dev/null || echo "")

LAST_FRONTEND_DEPLOY=$(gcloud run revisions list \
    --service=$SERVICE_NAME-frontend \
    --region=$GCP_REGION \
    --limit=1 \
    --format="value(metadata.creationTimestamp)" 2>/dev/null || echo "")

if [[ -n "$LAST_BACKEND_DEPLOY" ]]; then
    DEPLOY_AGE_SECONDS=$(( $(date +%s) - $(date -d "$LAST_BACKEND_DEPLOY" +%s) ))
    DEPLOY_AGE_MINUTES=$((DEPLOY_AGE_SECONDS / 60))
    
    if [[ $DEPLOY_AGE_MINUTES -lt 60 ]]; then
        log_warning "Déploiement récent détecté il y a ${DEPLOY_AGE_MINUTES} minutes"
        
        # Rollback immédiat si déploiement récent
        log_emergency "Rollback automatique en cours..."
        
        # Récupération révision précédente
        PREVIOUS_BACKEND=$(gcloud run revisions list \
            --service=$SERVICE_NAME-backend \
            --region=$GCP_REGION \
            --limit=2 \
            --format="value(metadata.name)" | tail -1)
        
        PREVIOUS_FRONTEND=$(gcloud run revisions list \
            --service=$SERVICE_NAME-frontend \
            --region=$GCP_REGION \
            --limit=2 \
            --format="value(metadata.name)" | tail -1)
        
        if [[ -n "$PREVIOUS_BACKEND" && -n "$PREVIOUS_FRONTEND" ]]; then
            # Rollback backend
            gcloud run services update-traffic $SERVICE_NAME-backend \
                --to-revisions=$PREVIOUS_BACKEND=100 \
                --region=$GCP_REGION
            
            # Rollback frontend  
            gcloud run services update-traffic $SERVICE_NAME-frontend \
                --to-revisions=$PREVIOUS_FRONTEND=100 \
                --region=$GCP_REGION
            
            log_success "Rollback effectué vers révisions précédentes"
            CORRECTIVE_ACTION="Rollback automatique effectué"
            
            # Attendre 30 secondes et re-tester
            log_info "Attente 30 secondes pour propagation..."
            sleep 30
            
            # Test post-rollback
            BACKEND_HTTP_CODE_POST=$(curl -o /dev/null -s -w "%{http_code}" --max-time 10 "$BACKEND_URL/api/health" || echo "000")
            if [[ "$BACKEND_HTTP_CODE_POST" == "200" ]]; then
                log_success "Rollback réussi - Backend opérationnel"
                CORRECTIVE_ACTION="$CORRECTIVE_ACTION - Backend restauré"
            else
                log_warning "Rollback insuffisant - Problème plus profond"
            fi
        else
            log_warning "Impossible de récupérer les révisions précédentes"
        fi
    fi
fi

# 3.2 Redémarrage forcé des services si nécessaire
if [[ "$BACKEND_HTTP_CODE" != "200" && -z "$CORRECTIVE_ACTION" ]]; then
    log_emergency "Redémarrage forcé du backend..."
    
    # Mise à 0 instances puis remontée
    gcloud run services update $SERVICE_NAME-backend \
        --min-instances=0 \
        --max-instances=0 \
        --region=$GCP_REGION
    
    sleep 10
    
    gcloud run services update $SERVICE_NAME-backend \
        --min-instances=1 \
        --max-instances=10 \
        --region=$GCP_REGION
    
    CORRECTIVE_ACTION="Redémarrage forcé backend"
fi

# 3.3 Activation mode dégradé si nécessaire
if [[ "$FRONTEND_HTTP_CODE" != "200" && "$BACKEND_HTTP_CODE" != "200" ]]; then
    log_emergency "Activation mode maintenance d'urgence..."
    
    # Déploiement page de maintenance
    if gsutil ls "$EMERGENCY_BUCKET/maintenance.html" >/dev/null 2>&1; then
        # Copie page de maintenance vers bucket principal
        gsutil cp "$EMERGENCY_BUCKET/maintenance.html" "gs://muscuscope-assets/maintenance.html"
        
        # Redirection via Cloud Functions ou Load Balancer (si configuré)
        log_warning "Page de maintenance déployée"
        CORRECTIVE_ACTION="$CORRECTIVE_ACTION + Page maintenance activée"
    else
        log_warning "Page de maintenance non trouvée dans $EMERGENCY_BUCKET"
    fi
fi

# Phase 4: Collecte d'informations diagnostiques
log_emergency "Phase 4: Collecte informations diagnostiques"

# 4.1 Logs d'erreur récents
log_info "Collecte logs d'erreur récents..."
ERROR_LOGS="/tmp/p0-error-logs-$INCIDENT_ID.log"

gcloud logging read "timestamp >= \"$(date -d '30 minutes ago' -Iseconds)\" AND severity>=ERROR" \
    --limit=50 \
    --format="table(timestamp,resource.labels.service_name,severity,textPayload)" \
    > "$ERROR_LOGS" 2>/dev/null || echo "Erreur collecte logs" > "$ERROR_LOGS"

# 4.2 Métriques récentes
METRICS_FILE="/tmp/p0-metrics-$INCIDENT_ID.log"
cat > "$METRICS_FILE" <<EOF
# Métriques P0 Incident - $INCIDENT_ID
Date: $(date)

## Status Services
Backend Status: $BACKEND_STATUS (HTTP: $BACKEND_HTTP_CODE)
Frontend Status: $FRONTEND_STATUS (HTTP: $FRONTEND_HTTP_CODE)
Database Status: $DB_STATUS

## Révisions actives
Backend: $(gcloud run services describe $SERVICE_NAME-backend --region=$GCP_REGION --format="value(status.traffic[0].revisionName)" 2>/dev/null || echo "N/A")
Frontend: $(gcloud run services describe $SERVICE_NAME-frontend --region=$GCP_REGION --format="value(status.traffic[0].revisionName)" 2>/dev/null || echo "N/A")

## Actions correctives
$CORRECTIVE_ACTION

## Prochaines étapes
- Continuer surveillance post-incident
- Identifier cause racine
- Post-mortem dans les 24h
EOF

# Phase 5: Notification mise à jour
log_emergency "Phase 5: Notification mise à jour"

UPDATE_MESSAGE="Incident P0 - Actions immédiates effectuées"
UPDATE_DETAILS="ID: $INCIDENT_ID
Cause probable: $PROBABLE_CAUSE
Actions: $CORRECTIVE_ACTION
Status actuel: Backend($BACKEND_HTTP_CODE) Frontend($FRONTEND_HTTP_CODE)
Prochaine update dans 15 minutes"

emergency_notify "$UPDATE_MESSAGE" "$UPDATE_DETAILS"

# Phase 6: Monitoring continu
log_emergency "Phase 6: Configuration monitoring continu"

# Script de surveillance continue
MONITOR_SCRIPT="/tmp/p0-monitor-$INCIDENT_ID.sh"
cat > "$MONITOR_SCRIPT" <<'EOF'
#!/bin/bash
# Monitoring continu incident P0

while true; do
    BACKEND_CODE=$(curl -o /dev/null -s -w "%{http_code}" --max-time 10 "https://api.muscuscope.com/api/health" || echo "000")
    FRONTEND_CODE=$(curl -o /dev/null -s -w "%{http_code}" --max-time 10 "https://muscuscope.com" || echo "000")
    
    echo "$(date): Backend=$BACKEND_CODE Frontend=$FRONTEND_CODE"
    
    if [[ "$BACKEND_CODE" == "200" && "$FRONTEND_CODE" == "200" ]]; then
        echo "$(date): ✅ Services restaurés - Arrêt monitoring"
        break
    fi
    
    sleep 30
done
EOF

chmod +x "$MONITOR_SCRIPT"
log_info "Script monitoring continu créé: $MONITOR_SCRIPT"

# Résumé final
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
log_emergency "RÉSUMÉ ACTIONS P0 - INCIDENT $INCIDENT_ID"
echo ""
echo "🕐 Début incident: $(date)"
echo "⏱️ Durée intervention: $SECONDS secondes"
echo ""
echo "📊 DIAGNOSTICS:"
echo "  • Backend: $BACKEND_STATUS (HTTP: $BACKEND_HTTP_CODE)"
echo "  • Frontend: $FRONTEND_STATUS (HTTP: $FRONTEND_HTTP_CODE)"  
echo "  • Database: $DB_STATUS"
echo ""
echo "🔧 ACTIONS EFFECTUÉES:"
echo "  • $CORRECTIVE_ACTION"
echo ""
echo "📁 FICHIERS GÉNÉRÉS:"
echo "  • Logs erreur: $ERROR_LOGS"
echo "  • Métriques: $METRICS_FILE"
echo "  • Monitor: $MONITOR_SCRIPT"
echo ""

if [[ "$BACKEND_HTTP_CODE" == "200" && "$FRONTEND_HTTP_CODE" == "200" ]]; then
    log_success "🎉 INCIDENT RÉSOLU - Services opérationnels"
    emergency_notify "Incident P0 RÉSOLU" "Services restaurés - Incident ID: $INCIDENT_ID"
else
    log_warning "⚠️ INCIDENT EN COURS - Intervention manuelle requise"
    echo ""
    echo "🚨 PROCHAINES ÉTAPES CRITIQUES:"
    echo "  1. Exécuter monitoring continu: $MONITOR_SCRIPT"
    echo "  2. Analyser logs: $ERROR_LOGS"
    echo "  3. Escalader si non résolu dans 15 minutes"
    echo "  4. Communiquer aux utilisateurs si nécessaire"
fi

echo ""
echo "📞 CONTACTS D'URGENCE:"
echo "  • Tech Lead: giovanni@muscuscope.com"
echo "  • DevOps: devops@muscuscope.com"
echo "  • Astreinte: +33 6 XX XX XX XX"

echo ""
echo "✅ Premières mesures P0 exécutées en $SECONDS secondes"
echo "👉 Continuer investigation manuelle et surveillance"
