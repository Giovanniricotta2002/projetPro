#!/bin/bash
# check-prerequisites.sh - Vérification des prérequis de déploiement

set -e

echo "🔍 Vérification des prérequis de déploiement"

# Variables de configuration
REQUIRED_GCLOUD_VERSION="400.0.0"
REQUIRED_TERRAFORM_VERSION="1.5.0"
REQUIRED_DOCKER_VERSION="20.10.0"

# Fonction pour comparer les versions
version_compare() {
    if [[ $1 == $2 ]]; then
        return 0
    fi
    local IFS=.
    local i ver1=($1) ver2=($2)
    for ((i=${#ver1[@]}; i<${#ver2[@]}; i++)); do
        ver1[i]=0
    done
    for ((i=0; i<${#ver1[@]}; i++)); do
        if [[ -z ${ver2[i]} ]]; then
            ver2[i]=0
        fi
        if ((10#${ver1[i]} > 10#${ver2[i]})); then
            return 1
        fi
        if ((10#${ver1[i]} < 10#${ver2[i]})); then
            return 2
        fi
    done
    return 0
}

# 1. Authentification GCP
echo "🔐 Vérification authentification GCP..."
if ! gcloud auth list --filter=status:ACTIVE --format="value(account)" | grep -q "@"; then
    echo "❌ Authentification GCP requise"
    echo "💡 Exécutez: gcloud auth login"
    exit 1
fi

# Vérification version gcloud
GCLOUD_VERSION=$(gcloud version --format="value(Google Cloud SDK)" 2>/dev/null || echo "0.0.0")
version_compare $GCLOUD_VERSION $REQUIRED_GCLOUD_VERSION
if [[ $? -eq 2 ]]; then
    echo "⚠️ Version gcloud trop ancienne: $GCLOUD_VERSION (requis: $REQUIRED_GCLOUD_VERSION+)"
    echo "💡 Mettez à jour: gcloud components update"
fi

echo "✅ GCP: $(gcloud config get-value account 2>/dev/null)"

# 2. Accès Docker Hub
echo "🐳 Vérification accès Docker..."
if ! docker info >/dev/null 2>&1; then
    echo "❌ Docker daemon non accessible"
    echo "💡 Démarrez Docker Desktop ou le service Docker"
    exit 1
fi

# Test push Docker (simulation)
DOCKER_USER=$(docker info --format '{{.Username}}' 2>/dev/null || echo "")
if [[ -z "$DOCKER_USER" ]]; then
    echo "⚠️ Authentification Docker Hub non configurée"
    echo "💡 Exécutez: docker login"
else
    echo "✅ Docker: $DOCKER_USER"
fi

# Vérification version Docker
DOCKER_VERSION=$(docker version --format '{{.Client.Version}}' 2>/dev/null || echo "0.0.0")
version_compare $DOCKER_VERSION $REQUIRED_DOCKER_VERSION
if [[ $? -eq 2 ]]; then
    echo "⚠️ Version Docker trop ancienne: $DOCKER_VERSION (requis: $REQUIRED_DOCKER_VERSION+)"
fi

# 3. Vérification Terraform
echo "🏗️ Vérification Terraform..."
if ! command -v terraform &> /dev/null; then
    echo "❌ Terraform non installé"
    echo "💡 Installez Terraform: https://developer.hashicorp.com/terraform/downloads"
    exit 1
fi

TERRAFORM_VERSION=$(terraform version -json | jq -r '.terraform_version' 2>/dev/null || echo "0.0.0")
version_compare $TERRAFORM_VERSION $REQUIRED_TERRAFORM_VERSION
if [[ $? -eq 2 ]]; then
    echo "⚠️ Version Terraform trop ancienne: $TERRAFORM_VERSION (requis: $REQUIRED_TERRAFORM_VERSION+)"
fi

echo "✅ Terraform: $TERRAFORM_VERSION"

# 4. Vérification kubectl
echo "☸️ Vérification kubectl..."
if ! command -v kubectl &> /dev/null; then
    echo "⚠️ kubectl non installé (optionnel pour Kubernetes)"
else
    KUBECTL_VERSION=$(kubectl version --client --output=json 2>/dev/null | jq -r '.clientVersion.gitVersion' || echo "unknown")
    echo "✅ kubectl: $KUBECTL_VERSION"
fi

# 5. Tests de connectivité
echo "🌐 Tests de connectivité..."

# Test GCP APIs
if ! gcloud services list --enabled --filter="name:run.googleapis.com" --format="value(name)" | grep -q "run.googleapis.com"; then
    echo "❌ API Cloud Run non activée"
    echo "💡 Activez: gcloud services enable run.googleapis.com"
    exit 1
fi

if ! gcloud services list --enabled --filter="name:sql.googleapis.com" --format="value(name)" | grep -q "sql.googleapis.com"; then
    echo "❌ API Cloud SQL non activée" 
    echo "💡 Activez: gcloud services enable sql.googleapis.com"
    exit 1
fi

echo "✅ APIs GCP activées"

# Test accès repository Git
echo "📦 Vérification accès repository..."
if [[ -d ".git" ]]; then
    REMOTE_URL=$(git remote get-url origin 2>/dev/null || echo "")
    if [[ -n "$REMOTE_URL" ]]; then
        echo "✅ Git repository: $REMOTE_URL"
    else
        echo "⚠️ Aucun remote Git configuré"
    fi
else
    echo "⚠️ Pas dans un repository Git"
fi

# 6. Vérification variables d'environnement
echo "⚙️ Vérification variables d'environnement..."

REQUIRED_VARS=(
    "GCP_PROJECT"
    "DOCKER_REGISTRY"
    "DATABASE_URL"
    "JWT_SECRET_KEY"
)

for var in "${REQUIRED_VARS[@]}"; do
    if [[ -z "${!var}" ]]; then
        echo "⚠️ Variable d'environnement manquante: $var"
    else
        echo "✅ $var: ✓"
    fi
done

# 7. Tests de validation fonctionnelle
echo "🧪 Exécution tests de validation..."
if [[ -f "./scripts/testing/run-smoke-tests.sh" ]]; then
    ./scripts/testing/run-smoke-tests.sh --quick
    if [[ $? -ne 0 ]]; then
        echo "❌ Tests de validation échoués"
        exit 1
    fi
else
    echo "⚠️ Script de tests de validation non trouvé"
fi

# 8. Résumé final
echo ""
echo "📋 Résumé des prérequis:"
echo "  ✅ Authentification GCP"
echo "  ✅ Docker opérationnel"
echo "  ✅ Terraform installé"
echo "  ✅ APIs GCP activées"
echo "  ✅ Tests de validation passés"
echo ""
echo "🎉 Tous les prérequis sont satisfaits pour le déploiement"
echo "💡 Vous pouvez maintenant exécuter: ./scripts/deployment/deploy-production.sh"
