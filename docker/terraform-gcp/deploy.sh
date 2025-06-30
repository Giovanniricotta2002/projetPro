#!/bin/bash

# Script de déploiement automatisé pour GCP
# Usage: ./deploy.sh

set -e

echo "🚀 Déploiement ProjetPro sur Google Cloud Platform"
echo "=================================================="

# Vérifications préalables
echo "🔍 Vérification des prérequis..."

# Vérifier gcloud
if ! command -v gcloud &> /dev/null; then
    echo "❌ gcloud CLI n'est pas installé"
    echo "📖 Installer: https://cloud.google.com/sdk/docs/install"
    exit 1
fi

# Vérifier terraform
if ! command -v terraform &> /dev/null; then
    echo "❌ Terraform n'est pas installé"
    echo "📖 Installer: https://learn.hashicorp.com/tutorials/terraform/install-cli"
    exit 1
fi

# Vérifier la connexion GCP
if ! gcloud auth list --filter=status:ACTIVE --format="value(account)" | head -n1 > /dev/null; then
    echo "❌ Pas de connexion GCP active"
    echo "🔑 Exécuter: gcloud auth login && gcloud auth application-default login"
    exit 1
fi

# Vérifier le fichier terraform.tfvars
if [ ! -f "terraform.tfvars" ]; then
    echo "⚠️  Fichier terraform.tfvars manquant"
    echo "📝 Création depuis l'exemple..."
    cp terraform.tfvars.example terraform.tfvars
    echo "✏️  Veuillez éditer terraform.tfvars avec vos valeurs puis relancer le script"
    exit 1
fi

# Vérifier que project_id est défini
if grep -q "your-gcp-project-id" terraform.tfvars; then
    echo "❌ Veuillez définir project_id dans terraform.tfvars"
    exit 1
fi

echo "✅ Prérequis vérifiés"

# Obtenir le project_id
PROJECT_ID=$(grep "project_id" terraform.tfvars | cut -d'"' -f2)
echo "📋 Projet: $PROJECT_ID"

# Définir le projet par défaut
gcloud config set project $PROJECT_ID

echo ""
echo "🔧 Initialisation Terraform..."
terraform init

echo ""
echo "📋 Planification du déploiement..."
terraform plan -out=tfplan

echo ""
echo "🚀 Application des changements..."
echo "⏱️  Cela peut prendre 10-15 minutes..."
terraform apply tfplan

echo ""
echo "✅ Déploiement terminé !"
echo ""

# Afficher les URLs
echo "🌐 URLs des services:"
terraform output -json | jq -r '
  "🖥️  Frontend: " + .frontend_url.value,
  "🔧 Backend: " + .backend_url.value,
  "📊 Grafana: " + .grafana_url.value
'

echo ""
echo "💡 Conseils:"
echo "   • Les services peuvent prendre quelques minutes à démarrer"
echo "   • Grafana login: admin / [votre mot de passe]"
echo "   • Surveillez les coûts dans la console GCP"
echo ""
echo "📖 Documentation complète: README.md"
