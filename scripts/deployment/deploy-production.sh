#!/bin/bash
# deploy-production.sh - Script de déploiement production

set -e

VERSION=${1:-"latest"}
ENVIRONMENT="production"
DRY_RUN=${DRY_RUN:-false}

# Configuration
GCP_PROJECT=${GCP_PROJECT:-"muscuscope-prod"}
GCP_REGION=${GCP_REGION:-"europe-west1"}
DOCKER_REGISTRY=${DOCKER_REGISTRY:-"giovanni2002ynov"}
SERVICE_NAME="muscuscope"

# Couleurs pour les logs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

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

# Fonction de rollback en cas d'erreur
rollback() {
    log_error "Erreur détectée, rollback en cours..."
    
    # Récupération de la version précédente
    PREVIOUS_VERSION=$(gcloud run revisions list \
        --service=$SERVICE_NAME-backend \
        --region=$GCP_REGION \
        --limit=2 \
        --format="value(metadata.name)" | tail -1)
    
    if [[ -n "$PREVIOUS_VERSION" ]]; then
        log_info "Rollback vers la révision: $PREVIOUS_VERSION"
        gcloud run services update-traffic $SERVICE_NAME-backend \
            --to-revisions=$PREVIOUS_VERSION=100 \
            --region=$GCP_REGION
        
        gcloud run services update-traffic $SERVICE_NAME-frontend \
            --to-revisions=$PREVIOUS_VERSION=100 \
            --region=$GCP_REGION
    fi
    
    exit 1
}

# Piège pour capturer les erreurs
trap rollback ERR

echo "🚀 Déploiement MuscuScope v$VERSION en $ENVIRONMENT"
echo "📅 Date: $(date)"
echo "🏷️ Version: $VERSION"
echo "🌍 Région: $GCP_REGION"

if [[ "$DRY_RUN" == "true" ]]; then
    log_warning "Mode DRY RUN activé - Aucune modification ne sera appliquée"
fi

# Phase 0: Vérifications préalables
log_info "Phase 0: Vérifications préalables"
./scripts/deployment/check-prerequisites.sh

# Phase 1: Build et Push des Images
log_info "Phase 1: Build et Push des Images Docker"

# Build Backend
log_info "Building backend image..."
if [[ "$DRY_RUN" != "true" ]]; then
    docker build -t $DOCKER_REGISTRY/$SERVICE_NAME:backend-$VERSION ./back/
    docker push $DOCKER_REGISTRY/$SERVICE_NAME:backend-$VERSION
fi
log_success "Backend image built and pushed"

# Build Frontend  
log_info "Building frontend image..."
if [[ "$DRY_RUN" != "true" ]]; then
    docker build -t $DOCKER_REGISTRY/$SERVICE_NAME:frontend-$VERSION ./front/
    docker push $DOCKER_REGISTRY/$SERVICE_NAME:frontend-$VERSION
fi
log_success "Frontend image built and pushed"

# Phase 2: Déploiement Infrastructure avec Terraform
log_info "Phase 2: Mise à jour infrastructure avec Terraform"

cd terraform-gcp/

if [[ "$DRY_RUN" != "true" ]]; then
    # Initialisation Terraform
    terraform init -upgrade
    
    # Plan de déploiement
    terraform plan \
        -var="app_version=$VERSION" \
        -var="project_id=$GCP_PROJECT" \
        -var="region=$GCP_REGION" \
        -out=production.tfplan
    
    # Application des changements
    terraform apply -auto-approve production.tfplan
else
    log_warning "DRY RUN: Terraform plan uniquement"
    terraform plan \
        -var="app_version=$VERSION" \
        -var="project_id=$GCP_PROJECT" \
        -var="region=$GCP_REGION"
fi

cd ..
log_success "Infrastructure mise à jour"

# Phase 3: Déploiement Backend API
log_info "Phase 3: Déploiement Backend API"

if [[ "$DRY_RUN" != "true" ]]; then
    gcloud run deploy $SERVICE_NAME-backend \
        --image=$DOCKER_REGISTRY/$SERVICE_NAME:backend-$VERSION \
        --region=$GCP_REGION \
        --platform=managed \
        --allow-unauthenticated \
        --memory=512Mi \
        --cpu=1 \
        --min-instances=1 \
        --max-instances=10 \
        --concurrency=80 \
        --timeout=300 \
        --set-env-vars="APP_ENV=prod,APP_VERSION=$VERSION" \
        --no-traffic
        
    # Test de santé avant bascule du trafic
    BACKEND_URL=$(gcloud run services describe $SERVICE_NAME-backend \
        --region=$GCP_REGION \
        --format="value(status.url)")
    
    log_info "Test de santé backend: $BACKEND_URL/api/health"
    
    for i in {1..5}; do
        if curl -f "$BACKEND_URL/api/health" >/dev/null 2>&1; then
            log_success "Backend health check OK"
            break
        fi
        log_warning "Tentative $i/5 - En attente..."
        sleep 10
    done
    
    # Bascule graduelle du trafic
    log_info "Bascule progressive du trafic backend..."
    gcloud run services update-traffic $SERVICE_NAME-backend \
        --to-latest=50 \
        --region=$GCP_REGION
    
    sleep 30
    
    gcloud run services update-traffic $SERVICE_NAME-backend \
        --to-latest=100 \
        --region=$GCP_REGION
else
    log_warning "DRY RUN: Simulation déploiement backend"
fi

log_success "Backend déployé"

# Phase 4: Déploiement Frontend
log_info "Phase 4: Déploiement Frontend"

if [[ "$DRY_RUN" != "true" ]]; then
    gcloud run deploy $SERVICE_NAME-frontend \
        --image=$DOCKER_REGISTRY/$SERVICE_NAME:frontend-$VERSION \
        --region=$GCP_REGION \
        --platform=managed \
        --allow-unauthenticated \
        --memory=256Mi \
        --cpu=1 \
        --min-instances=1 \
        --max-instances=5 \
        --concurrency=1000 \
        --timeout=30 \
        --set-env-vars="APP_VERSION=$VERSION,API_URL=$BACKEND_URL" \
        --no-traffic
    
    # Test de santé frontend
    FRONTEND_URL=$(gcloud run services describe $SERVICE_NAME-frontend \
        --region=$GCP_REGION \
        --format="value(status.url)")
    
    log_info "Test de santé frontend: $FRONTEND_URL"
    
    for i in {1..3}; do
        if curl -f "$FRONTEND_URL" >/dev/null 2>&1; then
            log_success "Frontend health check OK"
            break
        fi
        log_warning "Tentative $i/3 - En attente..."
        sleep 5
    done
    
    # Bascule du trafic frontend
    gcloud run services update-traffic $SERVICE_NAME-frontend \
        --to-latest=100 \
        --region=$GCP_REGION
else
    log_warning "DRY RUN: Simulation déploiement frontend"
fi

log_success "Frontend déployé"

# Phase 5: Tests Post-Déploiement
log_info "Phase 5: Tests Post-Déploiement"

if [[ "$DRY_RUN" != "true" ]]; then
    if [[ -f "./scripts/testing/post-deployment-tests.sh" ]]; then
        ./scripts/testing/post-deployment-tests.sh $ENVIRONMENT
    else
        log_warning "Script de tests post-déploiement non trouvé"
        
        # Tests basiques manuels
        log_info "Tests basiques de connectivité..."
        
        # Test API health
        if curl -f "$BACKEND_URL/api/health" >/dev/null 2>&1; then
            log_success "API health endpoint OK"
        else
            log_error "API health endpoint échoué"
            exit 1
        fi
        
        # Test frontend
        if curl -f "$FRONTEND_URL" >/dev/null 2>&1; then
            log_success "Frontend endpoint OK"
        else
            log_error "Frontend endpoint échoué"
            exit 1
        fi
    fi
else
    log_warning "DRY RUN: Simulation tests post-déploiement"
fi

log_success "Tests post-déploiement réussis"

# Phase 6: Mise à jour DNS et Load Balancer
log_info "Phase 6: Configuration DNS et Load Balancer"

if [[ "$DRY_RUN" != "true" ]]; then
    # Mise à jour du mapping de domaine
    gcloud run domain-mappings create \
        --service=$SERVICE_NAME-frontend \
        --domain=muscuscope.com \
        --region=$GCP_REGION \
        --force || true
    
    gcloud run domain-mappings create \
        --service=$SERVICE_NAME-backend \
        --domain=api.muscuscope.com \
        --region=$GCP_REGION \
        --force || true
else
    log_warning "DRY RUN: Simulation configuration DNS"
fi

log_success "DNS configuré"

# Phase 7: Notification
log_info "Phase 7: Notification des équipes"

DEPLOYMENT_SUMMARY="
🚀 **Déploiement MuscuScope v$VERSION** 
📅 **Date**: $(date)
🌍 **Environnement**: $ENVIRONMENT
✅ **Status**: SUCCESS

🔗 **URLs**:
- Frontend: https://muscuscope.com
- Backend API: https://api.muscuscope.com
- API Docs: https://api.muscuscope.com/doc

📊 **Métriques**:
- Durée déploiement: $SECONDS secondes
- Images déployées: 2
- Services mis à jour: 2
"

if [[ "$DRY_RUN" != "true" ]]; then
    if [[ -f "./scripts/monitoring/notify-deployment.sh" ]]; then
        ./scripts/monitoring/notify-deployment.sh "$VERSION" "$ENVIRONMENT" "SUCCESS" "$DEPLOYMENT_SUMMARY"
    else
        log_info "Notification manuelle requise"
        echo "$DEPLOYMENT_SUMMARY"
    fi
else
    log_warning "DRY RUN: Simulation notification"
    echo "$DEPLOYMENT_SUMMARY"
fi

# Phase 8: Nettoyage
log_info "Phase 8: Nettoyage post-déploiement"

if [[ "$DRY_RUN" != "true" ]]; then
    # Suppression des anciennes révisions (garde les 5 dernières)
    OLD_REVISIONS=$(gcloud run revisions list \
        --service=$SERVICE_NAME-backend \
        --region=$GCP_REGION \
        --sort-by="~metadata.creationTimestamp" \
        --format="value(metadata.name)" | tail -n +6)
    
    for revision in $OLD_REVISIONS; do
        log_info "Suppression ancienne révision: $revision"
        gcloud run revisions delete $revision --region=$GCP_REGION --quiet
    done
    
    # Nettoyage images Docker locales
    docker image prune -f
else
    log_warning "DRY RUN: Simulation nettoyage"
fi

# Désactivation du piège d'erreur
trap - ERR

log_success "✨ Déploiement MuscuScope v$VERSION terminé avec succès!"
log_info "🔗 Application disponible sur: https://muscuscope.com"
log_info "📊 Dashboard monitoring: https://grafana.muscuscope.com"
log_info "📈 Durée totale: $SECONDS secondes"

echo ""
echo "🎉 Déploiement réussi!"
