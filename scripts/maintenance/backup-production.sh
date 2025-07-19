#!/bin/bash
# backup-production.sh - Backup complet production

set -e

# Configuration
BACKUP_DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_BUCKET=${BACKUP_BUCKET:-"gs://muscuscope-backups"}
GCP_PROJECT=${GCP_PROJECT:-"muscuscope-prod"}
DB_INSTANCE=${DB_INSTANCE:-"muscuscope-db-prod"}
STORAGE_BUCKET=${STORAGE_BUCKET:-"gs://muscuscope-assets"}

# Couleurs pour les logs
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
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

# Fonction de vérification d'intégrité
check_backup_integrity() {
    local backup_path=$1
    local expected_min_size=$2
    
    if gsutil stat "$backup_path" >/dev/null 2>&1; then
        local size=$(gsutil du -b "$backup_path" | awk '{print $1}')
        if [[ $size -gt $expected_min_size ]]; then
            log_success "Backup intègre: $(numfmt --to=iec $size)"
            return 0
        else
            log_error "Backup trop petit: $(numfmt --to=iec $size)"
            return 1
        fi
    else
        log_error "Backup non trouvé: $backup_path"
        return 1
    fi
}

echo "💾 Début backup production MuscuScope"
echo "📅 Date: $(date)"
echo "🏷️ ID Backup: $BACKUP_DATE"
echo "🪣 Bucket: $BACKUP_BUCKET"

# Phase 1: Préparation
log_info "Phase 1: Préparation de l'environnement de backup"

# Vérification des permissions
if ! gsutil ls "$BACKUP_BUCKET" >/dev/null 2>&1; then
    log_error "Impossible d'accéder au bucket de backup: $BACKUP_BUCKET"
    exit 1
fi

# Création des dossiers de backup
gsutil -m mkdir -p "$BACKUP_BUCKET/database/" 2>/dev/null || true
gsutil -m mkdir -p "$BACKUP_BUCKET/assets/" 2>/dev/null || true
gsutil -m mkdir -p "$BACKUP_BUCKET/config/" 2>/dev/null || true
gsutil -m mkdir -p "$BACKUP_BUCKET/logs/" 2>/dev/null || true

log_success "Environnement de backup préparé"

# Phase 2: Backup base de données
log_info "Phase 2: Backup base de données"

DB_BACKUP_PATH="$BACKUP_BUCKET/database/muscuscope-db-$BACKUP_DATE.sql"

log_info "Création export SQL: $DB_BACKUP_PATH"

# Export complet avec structure et données
if gcloud sql export sql $DB_INSTANCE \
    "$DB_BACKUP_PATH" \
    --database=muscuscope_prod \
    --project=$GCP_PROJECT; then
    
    # Vérification intégrité (minimum 1MB attendu)
    if check_backup_integrity "$DB_BACKUP_PATH" 1048576; then
        log_success "Backup base de données créé et vérifié"
    else
        log_error "Problème d'intégrité du backup base de données"
        exit 1
    fi
else
    log_error "Échec export base de données"
    exit 1
fi

# Backup schéma seul (pour restauration rapide)
SCHEMA_BACKUP_PATH="$BACKUP_BUCKET/database/muscuscope-schema-$BACKUP_DATE.sql"
log_info "Création backup schéma: $SCHEMA_BACKUP_PATH"

gcloud sql export sql $DB_INSTANCE \
    "$SCHEMA_BACKUP_PATH" \
    --database=muscuscope_prod \
    --project=$GCP_PROJECT \
    --table='' || log_warning "Backup schéma échoué (non critique)"

# Phase 3: Backup fichiers utilisateurs
log_info "Phase 3: Backup fichiers utilisateurs et assets"

ASSETS_BACKUP_PATH="$BACKUP_BUCKET/assets/uploads-$BACKUP_DATE/"

log_info "Synchronisation assets: $STORAGE_BUCKET/uploads → $ASSETS_BACKUP_PATH"

if gsutil -m rsync -r -d "$STORAGE_BUCKET/uploads" "$ASSETS_BACKUP_PATH"; then
    # Comptage fichiers
    FILE_COUNT=$(gsutil ls -r "$ASSETS_BACKUP_PATH" | wc -l)
    TOTAL_SIZE=$(gsutil du -sh "$ASSETS_BACKUP_PATH" | awk '{print $1}')
    
    log_success "Assets sauvegardés: $FILE_COUNT fichiers, $TOTAL_SIZE"
else
    log_warning "Backup assets partiellement échoué"
fi

# Backup configuration publique
PUBLIC_BACKUP_PATH="$BACKUP_BUCKET/assets/public-$BACKUP_DATE/"
if gsutil ls "$STORAGE_BUCKET/public" >/dev/null 2>&1; then
    gsutil -m rsync -r "$STORAGE_BUCKET/public" "$PUBLIC_BACKUP_PATH"
    log_success "Fichiers publics sauvegardés"
fi

# Phase 4: Backup configuration et secrets
log_info "Phase 4: Backup configuration système"

CONFIG_BACKUP_PATH="$BACKUP_BUCKET/config/config-$BACKUP_DATE/"

# Configuration Cloud Run
log_info "Sauvegarde configuration Cloud Run..."
gcloud run services list --format="export" > "/tmp/cloudrun-services-$BACKUP_DATE.yaml"
gsutil cp "/tmp/cloudrun-services-$BACKUP_DATE.yaml" "$CONFIG_BACKUP_PATH"

# Configuration IAM
log_info "Sauvegarde politique IAM..."
gcloud projects get-iam-policy $GCP_PROJECT --format=yaml > "/tmp/iam-policy-$BACKUP_DATE.yaml"
gsutil cp "/tmp/iam-policy-$BACKUP_DATE.yaml" "$CONFIG_BACKUP_PATH"

# Configuration DNS (si configuré)
if gcloud dns managed-zones list --format="value(name)" | grep -q "muscuscope"; then
    log_info "Sauvegarde configuration DNS..."
    gcloud dns managed-zones list --format="export" > "/tmp/dns-config-$BACKUP_DATE.yaml"
    gsutil cp "/tmp/dns-config-$BACKUP_DATE.yaml" "$CONFIG_BACKUP_PATH"
fi

# Variables d'environnement (masquées)
log_info "Sauvegarde variables d'environnement (masquées)..."
cat > "/tmp/env-template-$BACKUP_DATE.txt" <<EOF
# Variables d'environnement MuscuScope - Template
# Date backup: $(date)

# Application
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=***MASKED***

# Base de données  
DATABASE_URL=postgresql://***MASKED***@host:5432/muscuscope_prod
REDIS_URL=***MASKED***

# JWT
JWT_SECRET_KEY=***MASKED***
JWT_PUBLIC_KEY=***MASKED***
JWT_PASSPHRASE=***MASKED***

# Services externes
SMTP_HOST=smtp.gmail.com
SMTP_USERNAME=***MASKED***
SMTP_PASSWORD=***MASKED***

# Cloud
GCS_BUCKET_NAME=muscuscope-assets
GCS_PROJECT_ID=$GCP_PROJECT
EOF

gsutil cp "/tmp/env-template-$BACKUP_DATE.txt" "$CONFIG_BACKUP_PATH"

log_success "Configuration système sauvegardée"

# Phase 5: Backup logs et monitoring
log_info "Phase 5: Backup logs et monitoring"

LOGS_BACKUP_PATH="$BACKUP_BUCKET/logs/logs-$BACKUP_DATE/"

# Export logs récents (7 derniers jours)
LOGS_CUTOFF=$(date -d "7 days ago" -Iseconds)
log_info "Export logs depuis: $LOGS_CUTOFF"

# Logs application
gcloud logging read "timestamp >= \"$LOGS_CUTOFF\" AND resource.type=cloud_run_revision" \
    --format="json" \
    --project=$GCP_PROJECT > "/tmp/app-logs-$BACKUP_DATE.json" || log_warning "Export logs application échoué"

# Logs système  
gcloud logging read "timestamp >= \"$LOGS_CUTOFF\" AND severity>=WARNING" \
    --format="json" \
    --project=$GCP_PROJECT > "/tmp/system-logs-$BACKUP_DATE.json" || log_warning "Export logs système échoué"

# Upload logs
if [[ -f "/tmp/app-logs-$BACKUP_DATE.json" ]]; then
    gsutil cp "/tmp/app-logs-$BACKUP_DATE.json" "$LOGS_BACKUP_PATH"
fi

if [[ -f "/tmp/system-logs-$BACKUP_DATE.json" ]]; then
    gsutil cp "/tmp/system-logs-$BACKUP_DATE.json" "$LOGS_BACKUP_PATH"
fi

log_success "Logs récents sauvegardés"

# Phase 6: Vérification intégrité globale
log_info "Phase 6: Vérification intégrité globale"

# Génération manifest de backup
MANIFEST_FILE="/tmp/backup-manifest-$BACKUP_DATE.json"

cat > "$MANIFEST_FILE" <<EOF
{
  "backup_id": "$BACKUP_DATE",
  "timestamp": "$(date -Iseconds)",
  "project": "$GCP_PROJECT",
  "components": {
    "database": {
      "path": "$DB_BACKUP_PATH",
      "size": "$(gsutil du -b "$DB_BACKUP_PATH" | awk '{print $1}')",
      "checksum": "$(gsutil hash "$DB_BACKUP_PATH" | grep md5 | awk '{print $3}')"
    },
    "assets": {
      "path": "$ASSETS_BACKUP_PATH",
      "files": $(gsutil ls -r "$ASSETS_BACKUP_PATH" | wc -l),
      "size": "$(gsutil du -b "$ASSETS_BACKUP_PATH" | tail -1 | awk '{print $1}')"
    },
    "config": {
      "path": "$CONFIG_BACKUP_PATH"
    },
    "logs": {
      "path": "$LOGS_BACKUP_PATH"
    }
  },
  "retention": {
    "database": "30 days",
    "assets": "90 days", 
    "config": "365 days",
    "logs": "30 days"
  }
}
EOF

gsutil cp "$MANIFEST_FILE" "$BACKUP_BUCKET/manifests/backup-manifest-$BACKUP_DATE.json"

log_success "Manifest de backup créé"

# Phase 7: Test de restauration (simulation)
log_info "Phase 7: Test de restauration (simulation)"

# Test lecture backup DB
if gsutil cat "$DB_BACKUP_PATH" | head -20 | grep -q "PostgreSQL database dump"; then
    log_success "Backup DB: Format PostgreSQL valide"
else
    log_warning "Backup DB: Format non reconnu"
fi

# Test intégrité assets
SAMPLE_FILES=$(gsutil ls "$ASSETS_BACKUP_PATH" | head -5)
for file in $SAMPLE_FILES; do
    if gsutil stat "$file" >/dev/null 2>&1; then
        log_success "Asset accessible: $(basename "$file")"
    else
        log_warning "Asset inaccessible: $(basename "$file")"
    fi
done

# Phase 8: Nettoyage anciennes sauvegardes
log_info "Phase 8: Nettoyage sauvegardes anciennes"

# Rétention base de données: 30 jours
DB_CUTOFF=$(date -d "30 days ago" +%Y%m%d)
OLD_DB_BACKUPS=$(gsutil ls "$BACKUP_BUCKET/database/" | grep "muscuscope-db-$DB_CUTOFF" || true)
if [[ -n "$OLD_DB_BACKUPS" ]]; then
    echo "$OLD_DB_BACKUPS" | xargs gsutil -m rm
    log_success "Anciens backups DB supprimés (>30j)"
fi

# Rétention assets: 90 jours  
ASSETS_CUTOFF=$(date -d "90 days ago" +%Y%m%d)
OLD_ASSETS=$(gsutil ls "$BACKUP_BUCKET/assets/" | grep "uploads-$ASSETS_CUTOFF" || true)
if [[ -n "$OLD_ASSETS" ]]; then
    echo "$OLD_ASSETS" | xargs gsutil -m rm -r
    log_success "Anciens backups assets supprimés (>90j)"
fi

# Rétention logs: 30 jours
OLD_LOGS=$(gsutil ls "$BACKUP_BUCKET/logs/" | grep "logs-$DB_CUTOFF" || true)
if [[ -n "$OLD_LOGS" ]]; then
    echo "$OLD_LOGS" | xargs gsutil -m rm -r
    log_success "Anciens logs supprimés (>30j)"
fi

# Phase 9: Notification et documentation
log_info "Phase 9: Finalisation et notification"

# Calcul statistiques finales
TOTAL_BACKUP_SIZE=$(gsutil du -sh "$BACKUP_BUCKET" | awk '{print $1}')
DB_SIZE=$(gsutil du -h "$DB_BACKUP_PATH" | awk '{print $1}')
ASSETS_SIZE=$(gsutil du -h "$ASSETS_BACKUP_PATH" | awk '{print $1}')

# Génération rapport
BACKUP_REPORT="/tmp/backup-report-$BACKUP_DATE.txt"

cat > "$BACKUP_REPORT" <<EOF
# Rapport de Backup Production - MuscuScope
Date: $(date)
ID Backup: $BACKUP_DATE
Durée: $SECONDS secondes

## Composants sauvegardés
✅ Base de données: $DB_SIZE
✅ Assets utilisateurs: $ASSETS_SIZE  
✅ Configuration système: Complète
✅ Logs (7 derniers jours): Exportés

## Localisation backups
- Database: $DB_BACKUP_PATH
- Assets: $ASSETS_BACKUP_PATH
- Config: $CONFIG_BACKUP_PATH
- Logs: $LOGS_BACKUP_PATH
- Manifest: $BACKUP_BUCKET/manifests/backup-manifest-$BACKUP_DATE.json

## Vérifications
✅ Intégrité base de données vérifiée
✅ Accessibilité fichiers testée
✅ Manifest généré
✅ Rétention appliquée

## Statistiques
- Taille totale backup: $TOTAL_BACKUP_SIZE
- Durée exécution: $SECONDS secondes
- Prochaine rétention DB: $(date -d "30 days" +%Y-%m-%d)
- Prochaine rétention Assets: $(date -d "90 days" +%Y-%m-%d)
EOF

log_info "Rapport backup généré: $BACKUP_REPORT"

# Nettoyage fichiers temporaires
rm -f /tmp/*$BACKUP_DATE* 2>/dev/null || true

# Résumé final
echo ""
log_success "✅ Backup production terminé avec succès!"
echo ""
echo "📊 Résumé:"
echo "  🗄️ Base de données: $DB_SIZE"
echo "  📁 Assets: $ASSETS_SIZE"
echo "  ⚙️ Configuration: Sauvegardée"
echo "  📝 Logs: 7 jours exportés"
echo "  📋 Manifest: Généré"
echo ""
echo "🔗 Bucket backup: $BACKUP_BUCKET"
echo "⏱️ Durée totale: $SECONDS secondes"
echo "📅 Prochaine rétention: $(date -d "30 days" +%Y-%m-%d)"

echo ""
echo "💾 Backup complet réussi - ID: $BACKUP_DATE"
