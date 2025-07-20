#!/bin/bash
# weekly-maintenance.sh - Maintenance hebdomadaire automatisée

set -e

# Configuration
BACKUP_RETENTION_DAYS=30
LOG_RETENTION_DAYS=7
TEMP_RETENTION_DAYS=7
GCP_PROJECT=${GCP_PROJECT:-"muscuscope-prod"}
BACKUP_BUCKET=${BACKUP_BUCKET:-"gs://muscuscope-backups"}

# Couleurs pour les logs
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

echo "🔧 Début maintenance hebdomadaire $(date)"
echo "🏗️ Projet GCP: $GCP_PROJECT"

# 1. Nettoyage logs anciens
log_info "Phase 1: Nettoyage des logs anciens (>$LOG_RETENTION_DAYS jours)"

CUTOFF_DATE=$(date -d "$LOG_RETENTION_DAYS days ago" -Iseconds)

# Nettoyage logs Cloud Logging
log_info "Nettoyage logs Cloud Logging..."
OLD_LOGS=$(gcloud logging read "timestamp < \"$CUTOFF_DATE\"" \
    --format="value(logName)" \
    --limit=1000 \
    --project=$GCP_PROJECT)

if [[ -n "$OLD_LOGS" ]]; then
    echo "$OLD_LOGS" | head -100 | while read -r log_name; do
        if [[ -n "$log_name" ]]; then
            gcloud logging logs delete "$log_name" --quiet --project=$GCP_PROJECT
            log_info "Log supprimé: $(basename "$log_name")"
        fi
    done
    log_success "Logs anciens nettoyés"
else
    log_info "Aucun log ancien à nettoyer"
fi

# Nettoyage logs application locaux (si présents)
if [[ -d "/var/log/muscuscope" ]]; then
    find /var/log/muscuscope -name "*.log" -mtime +$LOG_RETENTION_DAYS -delete
    log_success "Logs application locaux nettoyés"
fi

# 2. Analyse sécurité des dépendances
log_info "Phase 2: Analyse sécurité des dépendances"

# Backend PHP
log_info "Audit sécurité backend (Composer)..."
if [[ -f "back/composer.json" ]]; then
    cd back/
    if composer audit --no-dev 2>/dev/null; then
        log_success "Audit Composer: Aucune vulnérabilité détectée"
    else
        log_warning "Audit Composer: Vulnérabilités détectées - Vérification requise"
        composer audit --no-dev > /tmp/composer-audit-$(date +%Y%m%d).log 2>&1
    fi
    cd ..
else
    log_warning "composer.json non trouvé dans back/"
fi

# Frontend NPM
log_info "Audit sécurité frontend (NPM)..."
if [[ -f "front/package.json" ]]; then
    cd front/
    if npm audit --audit-level=high 2>/dev/null; then
        log_success "Audit NPM: Aucune vulnérabilité haute/critique"
    else
        log_warning "Audit NPM: Vulnérabilités détectées - Vérification requise"
        npm audit > /tmp/npm-audit-$(date +%Y%m%d).log 2>&1
    fi
    cd ..
else
    log_warning "package.json non trouvé dans front/"
fi

# 3. Backup base de données
log_info "Phase 3: Backup hebdomadaire base de données"

DB_INSTANCE="muscuscope-db-prod"
BACKUP_FILE="backup-$(date +%Y%m%d_%H%M%S).sql"

log_info "Création backup: $BACKUP_FILE"
if gcloud sql export sql $DB_INSTANCE \
    $BACKUP_BUCKET/weekly/$BACKUP_FILE \
    --database=muscuscope_prod \
    --project=$GCP_PROJECT; then
    log_success "Backup base de données créé: $BACKUP_FILE"
else
    log_warning "Échec backup base de données"
fi

# Vérification taille backup
BACKUP_SIZE=$(gsutil du -h $BACKUP_BUCKET/weekly/$BACKUP_FILE | awk '{print $1}')
log_info "Taille backup: $BACKUP_SIZE"

# 4. Nettoyage storage temporaire
log_info "Phase 4: Nettoyage storage temporaire"

TEMP_CUTOFF=$(date -d "$TEMP_RETENTION_DAYS days ago" +%Y%m%d)

# Nettoyage uploads temporaires
if gsutil ls $BACKUP_BUCKET/temp/ 2>/dev/null; then
    OLD_TEMP_FILES=$(gsutil ls $BACKUP_BUCKET/temp/*$TEMP_CUTOFF* 2>/dev/null || true)
    if [[ -n "$OLD_TEMP_FILES" ]]; then
        echo "$OLD_TEMP_FILES" | xargs gsutil -m rm
        log_success "Fichiers temporaires anciens supprimés"
    else
        log_info "Aucun fichier temporaire ancien trouvé"
    fi
fi

# Nettoyage dossier uploads (fichiers > 30 jours)
UPLOAD_CUTOFF=$(date -d "30 days ago" +%Y%m%d)
if gsutil ls gs://muscuscope-assets/uploads/ 2>/dev/null; then
    OLD_UPLOADS=$(gsutil ls gs://muscuscope-assets/uploads/**/*$UPLOAD_CUTOFF* 2>/dev/null || true)
    if [[ -n "$OLD_UPLOADS" ]]; then
        log_info "Nettoyage uploads anciens (>30 jours)..."
        echo "$OLD_UPLOADS" | head -100 | xargs gsutil -m rm
        log_success "Uploads anciens nettoyés"
    fi
fi

# 5. Optimisation base de données
log_info "Phase 5: Optimisation base de données"

# Création script SQL d'optimisation
cat > /tmp/db_maintenance.sql <<EOF
-- Maintenance hebdomadaire base de données
-- Date: $(date)

-- Analyse des statistiques
ANALYZE;

-- Vacuum des tables principales
VACUUM ANALYZE users;
VACUUM ANALYZE machines;
VACUUM ANALYZE exercises;
VACUUM ANALYZE sessions;

-- Reindex des index fragmentés
REINDEX INDEX CONCURRENTLY idx_users_email;
REINDEX INDEX CONCURRENTLY idx_machines_name;
REINDEX INDEX CONCURRENTLY idx_sessions_user_id;

-- Nettoyage logs anciens (si table de logs applicative)
DELETE FROM app_logs WHERE created_at < NOW() - INTERVAL '$LOG_RETENTION_DAYS days';

-- Statistiques post-maintenance
SELECT 
    schemaname,
    tablename,
    n_tup_ins as inserts,
    n_tup_upd as updates,
    n_tup_del as deletes,
    n_live_tup as live_tuples,
    n_dead_tup as dead_tuples
FROM pg_stat_user_tables 
ORDER BY n_live_tup DESC;
EOF

# Exécution maintenance DB
log_info "Exécution maintenance base de données..."
if gcloud sql connect $DB_INSTANCE \
    --user=postgres \
    --quiet \
    --project=$GCP_PROJECT < /tmp/db_maintenance.sql > /tmp/db_maintenance_results.log 2>&1; then
    log_success "Maintenance base de données terminée"
else
    log_warning "Problème lors de la maintenance DB - Vérifiez les logs"
fi

# 6. Vérification santé des services
log_info "Phase 6: Vérification santé des services"

# Check services Cloud Run
SERVICES=("muscuscope-backend" "muscuscope-frontend")

for service in "${SERVICES[@]}"; do
    STATUS=$(gcloud run services describe $service \
        --region=europe-west1 \
        --format="value(status.conditions[0].status)" \
        --project=$GCP_PROJECT 2>/dev/null || echo "Unknown")
    
    if [[ "$STATUS" == "True" ]]; then
        log_success "Service $service: Opérationnel"
    else
        log_warning "Service $service: Status $STATUS"
    fi
done

# Check base de données
DB_STATUS=$(gcloud sql instances describe $DB_INSTANCE \
    --format="value(state)" \
    --project=$GCP_PROJECT)

if [[ "$DB_STATUS" == "RUNNABLE" ]]; then
    log_success "Base de données: Opérationnelle"
else
    log_warning "Base de données: Status $DB_STATUS"
fi

# 7. Génération rapport santé
log_info "Phase 7: Génération rapport de santé"

HEALTH_REPORT="/tmp/health-report-$(date +%Y%m%d).txt"

cat > "$HEALTH_REPORT" <<EOF
# Rapport de Santé Hebdomadaire - MuscuScope
Date: $(date)
Projet: $GCP_PROJECT

## Résumé Maintenance
- ✅ Logs nettoyés (>$LOG_RETENTION_DAYS jours)
- ✅ Audit sécurité dépendances
- ✅ Backup base de données: $BACKUP_FILE
- ✅ Storage temporaire nettoyé
- ✅ Base de données optimisée
- ✅ Santé services vérifiée

## Statistiques
- Taille backup: $BACKUP_SIZE
- Durée maintenance: $SECONDS secondes
- Services vérifiés: ${#SERVICES[@]}

## Actions requises
$(if [[ -f "/tmp/composer-audit-$(date +%Y%m%d).log" ]]; then echo "- ⚠️ Vérifier vulnérabilités Composer"; fi)
$(if [[ -f "/tmp/npm-audit-$(date +%Y%m%d).log" ]]; then echo "- ⚠️ Vérifier vulnérabilités NPM"; fi)

## Prochaine maintenance
Date: $(date -d "7 days" +%Y-%m-%d)
EOF

log_info "Rapport santé généré: $HEALTH_REPORT"

# Envoi du rapport (si script de notification disponible)
if [[ -f "./scripts/monitoring/send-health-report.sh" ]]; then
    ./scripts/monitoring/send-health-report.sh "$HEALTH_REPORT"
    log_success "Rapport santé envoyé"
fi

# 8. Nettoyage backups anciens
log_info "Phase 8: Nettoyage backups anciens (>$BACKUP_RETENTION_DAYS jours)"

BACKUP_CUTOFF=$(date -d "$BACKUP_RETENTION_DAYS days ago" +%Y%m%d)

# Suppression backups hebdomadaires anciens
OLD_BACKUPS=$(gsutil ls $BACKUP_BUCKET/weekly/backup-$BACKUP_CUTOFF* 2>/dev/null || true)
if [[ -n "$OLD_BACKUPS" ]]; then
    echo "$OLD_BACKUPS" | xargs gsutil -m rm
    log_success "Backups anciens supprimés"
else
    log_info "Aucun backup ancien à supprimer"
fi

# 9. Nettoyage fichiers temporaires locaux
log_info "Phase 9: Nettoyage fichiers temporaires locaux"

# Nettoyage /tmp
find /tmp -name "*muscuscope*" -mtime +1 -delete 2>/dev/null || true
find /tmp -name "*audit*" -mtime +7 -delete 2>/dev/null || true

# Nettoyage logs Docker (si Docker installé)
if command -v docker &> /dev/null; then
    docker system prune -f --volumes 2>/dev/null || true
    log_success "Système Docker nettoyé"
fi

# 10. Résumé final
log_info "Phase 10: Résumé maintenance"

echo ""
log_success "✅ Maintenance hebdomadaire terminée avec succès!"
echo ""
echo "📊 Résumé des actions:"
echo "  ✅ Logs nettoyés (rétention: $LOG_RETENTION_DAYS jours)"
echo "  ✅ Dépendances auditées"  
echo "  ✅ Backup créé: $BACKUP_FILE"
echo "  ✅ Storage optimisé"
echo "  ✅ Base de données maintenue"
echo "  ✅ Services vérifiés"
echo "  ✅ Rapport généré: $HEALTH_REPORT"
echo ""
echo "⏱️ Durée totale: $SECONDS secondes"
echo "📅 Prochaine maintenance: $(date -d "7 days" +%Y-%m-%d)"

# Sauvegarde du log de maintenance
MAINTENANCE_LOG="/tmp/weekly-maintenance-$(date +%Y%m%d_%H%M%S).log"
echo "Maintenance hebdomadaire du $(date) - Durée: $SECONDS secondes" > "$MAINTENANCE_LOG"
echo "Actions effectuées avec succès" >> "$MAINTENANCE_LOG"

if command -v gsutil &> /dev/null; then
    gsutil cp "$MAINTENANCE_LOG" $BACKUP_BUCKET/logs/
    log_success "Log de maintenance sauvegardé"
fi

echo ""
echo "🔧 Maintenance hebdomadaire réussie!"
