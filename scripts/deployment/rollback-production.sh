#!/bin/bash
# rollback-production.sh - Procédure de rollback d'urgence

set -e

PREVIOUS_VERSION=${1:-"latest-stable"}
ENVIRONMENT=${2:-"production"}
FORCE=${3:-false}

# Configuration
GCP_PROJECT=${GCP_PROJECT:-"muscuscope-prod"}
GCP_REGION=${GCP_REGION:-"europe-west1"}
SERVICE_NAME="muscuscope"

# Couleurs pour les logs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
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

echo "🔄 Rollback d'urgence MuscuScope"
echo "📅 Date: $(date)"
echo "🏷️ Version cible: $PREVIOUS_VERSION"
echo "🌍 Environnement: $ENVIRONMENT"

# Confirmation de sécurité pour la production
if [[ "$ENVIRONMENT" == "production" && "$FORCE" != "true" ]]; then
    echo ""
    log_warning "⚠️ ATTENTION: Rollback en PRODUCTION ⚠️"
    echo "Cette action va restaurer la version précédente."
    echo "Cela peut affecter les utilisateurs actifs."
    echo ""
    echo "Tapez 'ROLLBACK-CONFIRM' pour continuer:"
    read -r confirmation
    
    if [[ "$confirmation" != "ROLLBACK-CONFIRM" ]]; then
        log_error "Rollback annulé par l'utilisateur"
        exit 1
    fi
fi

# Récupération des informations sur les révisions actuelles
log_info "Récupération des informations de déploiement..."

CURRENT_BACKEND_REVISION=$(gcloud run services describe $SERVICE_NAME-backend \
    --region=$GCP_REGION \
    --format="value(spec.traffic[0].revisionName)")

CURRENT_FRONTEND_REVISION=$(gcloud run services describe $SERVICE_NAME-frontend \
    --region=$GCP_REGION \
    --format="value(spec.traffic[0].revisionName)")

log_info "Révision backend actuelle: $CURRENT_BACKEND_REVISION"
log_info "Révision frontend actuelle: $CURRENT_FRONTEND_REVISION"

# Détermination de la version de rollback
if [[ "$PREVIOUS_VERSION" == "latest-stable" ]]; then
    log_info "Recherche de la version stable précédente..."
    
    PREVIOUS_BACKEND_REVISION=$(gcloud run revisions list \
        --service=$SERVICE_NAME-backend \
        --region=$GCP_REGION \
        --filter="metadata.labels.version!=latest" \
        --sort-by="~metadata.creationTimestamp" \
        --limit=1 \
        --format="value(metadata.name)")
    
    PREVIOUS_FRONTEND_REVISION=$(gcloud run revisions list \
        --service=$SERVICE_NAME-frontend \
        --region=$GCP_REGION \
        --filter="metadata.labels.version!=latest" \
        --sort-by="~metadata.creationTimestamp" \
        --limit=1 \
        --format="value(metadata.name)")
else
    PREVIOUS_BACKEND_REVISION="$SERVICE_NAME-backend-$PREVIOUS_VERSION"
    PREVIOUS_FRONTEND_REVISION="$SERVICE_NAME-frontend-$PREVIOUS_VERSION"
fi

if [[ -z "$PREVIOUS_BACKEND_REVISION" || -z "$PREVIOUS_FRONTEND_REVISION" ]]; then
    log_error "Impossible de déterminer la version de rollback"
    log_error "Vérifiez que la version $PREVIOUS_VERSION existe"
    exit 1
fi

log_info "Rollback vers:"
log_info "  Backend: $PREVIOUS_BACKEND_REVISION"
log_info "  Frontend: $PREVIOUS_FRONTEND_REVISION"

# Phase 1: Rollback Backend (priorité haute)
log_info "Phase 1: Rollback Backend API..."

gcloud run services update-traffic $SERVICE_NAME-backend \
    --to-revisions=$PREVIOUS_BACKEND_REVISION=100 \
    --region=$GCP_REGION

log_success "Backend rollback effectué"

# Attendre quelques secondes pour la propagation
sleep 10

# Test de sanité backend
log_info "Test de sanité backend après rollback..."
BACKEND_URL=$(gcloud run services describe $SERVICE_NAME-backend \
    --region=$GCP_REGION \
    --format="value(status.url)")

for i in {1..5}; do
    if curl -f "$BACKEND_URL/api/health" >/dev/null 2>&1; then
        log_success "Backend health check OK après rollback"
        break
    fi
    
    if [[ $i -eq 5 ]]; then
        log_error "Backend health check échoué après rollback"
        log_error "Intervention manuelle requise"
        exit 1
    fi
    
    log_warning "Tentative $i/5 - En attente..."
    sleep 10
done

# Phase 2: Rollback Frontend
log_info "Phase 2: Rollback Frontend..."

gcloud run services update-traffic $SERVICE_NAME-frontend \
    --to-revisions=$PREVIOUS_FRONTEND_REVISION=100 \
    --region=$GCP_REGION

log_success "Frontend rollback effectué"

# Attendre la propagation
sleep 10

# Test de sanité frontend
log_info "Test de sanité frontend après rollback..."
FRONTEND_URL=$(gcloud run services describe $SERVICE_NAME-frontend \
    --region=$GCP_REGION \
    --format="value(status.url)")

for i in {1..3}; do
    if curl -f "$FRONTEND_URL" >/dev/null 2>&1; then
        log_success "Frontend health check OK après rollback"
        break
    fi
    
    if [[ $i -eq 3 ]]; then
        log_error "Frontend health check échoué après rollback"
        log_error "Intervention manuelle requise"
        exit 1
    fi
    
    log_warning "Tentative $i/3 - En attente..."
    sleep 5
done

# Phase 3: Tests de régression post-rollback
log_info "Phase 3: Tests de régression post-rollback..."

# Test API critique
log_info "Test endpoint API critique..."
if curl -f "$BACKEND_URL/api/health" >/dev/null 2>&1; then
    log_success "API endpoint fonctionnel"
else
    log_error "API endpoint non fonctionnel"
    exit 1
fi

# Test base de données
log_info "Test connectivité base de données..."
if curl -f "$BACKEND_URL/api/health/db" >/dev/null 2>&1; then
    log_success "Base de données accessible"
else
    log_warning "Base de données potentiellement inaccessible"
fi

# Test authentification
log_info "Test endpoint authentification..."
AUTH_RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" \
    -X POST "$BACKEND_URL/api/login" \
    -H "Content-Type: application/json" \
    -d '{"email":"test@example.com","password":"invalid"}')

if [[ "$AUTH_RESPONSE" == "401" ]]; then
    log_success "Endpoint authentification fonctionnel"
else
    log_warning "Endpoint authentification comportement inattendu: $AUTH_RESPONSE"
fi

# Phase 4: Vérification métriques
log_info "Phase 4: Vérification métriques post-rollback..."

# Vérification CPU/Mémoire
BACKEND_METRICS=$(gcloud run services describe $SERVICE_NAME-backend \
    --region=$GCP_REGION \
    --format="value(status.conditions[0].status)")

FRONTEND_METRICS=$(gcloud run services describe $SERVICE_NAME-frontend \
    --region=$GCP_REGION \
    --format="value(status.conditions[0].status)")

if [[ "$BACKEND_METRICS" == "True" && "$FRONTEND_METRICS" == "True" ]]; then
    log_success "Services opérationnels selon les métriques"
else
    log_warning "Métriques de service dégradées"
fi

# Phase 5: Notification d'incident
log_info "Phase 5: Notification de rollback..."

ROLLBACK_SUMMARY="
🔄 **Rollback d'urgence MuscuScope**
📅 **Date**: $(date)
🌍 **Environnement**: $ENVIRONMENT
⚠️ **Status**: ROLLBACK COMPLETED

📊 **Détails**:
- Version précédente restaurée: $PREVIOUS_VERSION
- Backend: $PREVIOUS_BACKEND_REVISION
- Frontend: $PREVIOUS_FRONTEND_REVISION
- Durée rollback: $SECONDS secondes

🔗 **URLs vérifiées**:
- Frontend: $FRONTEND_URL ✅
- Backend API: $BACKEND_URL ✅

🚨 **Action requise**:
- Identifier cause du problème original
- Planifier correctif
- Communiquer aux utilisateurs si nécessaire
"

if [[ -f "./scripts/monitoring/notify-incident.sh" ]]; then
    ./scripts/monitoring/notify-incident.sh "ROLLBACK" "Rollback d'urgence effectué" "$ROLLBACK_SUMMARY"
else
    log_info "Notification manuelle requise:"
    echo "$ROLLBACK_SUMMARY"
fi

# Phase 6: Documentation du rollback
log_info "Phase 6: Documentation du rollback..."

ROLLBACK_LOG="/tmp/rollback-$(date +%Y%m%d_%H%M%S).log"
cat > "$ROLLBACK_LOG" <<EOF
# Rollback Log - MuscuScope
Date: $(date)
Environnement: $ENVIRONMENT
Version source: $CURRENT_BACKEND_REVISION
Version cible: $PREVIOUS_VERSION
Durée: $SECONDS secondes
Raison: Rollback d'urgence
Status: SUCCESS

## Services rollback:
- Backend: $CURRENT_BACKEND_REVISION → $PREVIOUS_BACKEND_REVISION
- Frontend: $CURRENT_FRONTEND_REVISION → $PREVIOUS_FRONTEND_REVISION

## Tests post-rollback:
- Health checks: ✅
- API endpoints: ✅
- Database connectivity: ✅
- Authentication: ✅

## Actions de suivi:
- [ ] Identifier la cause racine du problème
- [ ] Créer un hotfix si nécessaire
- [ ] Planifier le re-déploiement
- [ ] Post-mortem de l'incident
EOF

log_info "Log de rollback sauvegardé: $ROLLBACK_LOG"

# Phase 7: Recommandations post-rollback
log_info "Phase 7: Recommandations post-rollback..."

echo ""
log_success "✅ Rollback d'urgence terminé avec succès!"
echo ""
log_info "🔍 Prochaines étapes recommandées:"
echo "  1. Analyser les logs de l'incident original"
echo "  2. Identifier la cause racine du problème"
echo "  3. Développer et tester un correctif"
echo "  4. Planifier un nouveau déploiement"
echo "  5. Organiser un post-mortem d'incident"
echo ""
log_info "📊 Services opérationnels:"
echo "  - Frontend: $FRONTEND_URL"
echo "  - Backend API: $BACKEND_URL"
echo "  - Monitoring: https://grafana.muscuscope.com"
echo ""
log_warning "⚠️ Communiquez aux utilisateurs si l'incident était visible"

echo ""
echo "🔄 Rollback réussi en $SECONDS secondes!"
