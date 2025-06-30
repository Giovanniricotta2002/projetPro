#!/bin/bash

# Script de déploiement Grafana pour Kubernetes
# Usage: ./deploy-grafana.sh

set -e

echo "🚀 Déploiement de Grafana..."

# Vérifier que nous sommes dans le bon répertoire
if [ ! -f "configmap.yaml" ]; then
    echo "❌ Erreur: Ce script doit être exécuté dans le répertoire kub-grafana"
    exit 1
fi

# Appliquer les ressources dans l'ordre
echo "📦 Application du PVC..."
kubectl apply -f pvc.yaml

echo "🔧 Application de la ConfigMap..."
kubectl apply -f configmap.yaml

echo "🔐 Application des Secrets..."
kubectl apply -f secret.yaml

echo "🚀 Application du Service..."
kubectl apply -f service.yaml

echo "📱 Application du Deployment..."
kubectl apply -f deployment.yaml

echo "🌐 Application de la Route HTTP..."
kubectl apply -f httproute.yaml

echo "✅ Déploiement terminé !"
echo ""
echo "📊 Accès à Grafana:"
echo "   URL: https://grafana.muscuscope.local"
echo "   Utilisateur: admin"
echo "   Mot de passe: admin (à changer en production !)"
echo ""
echo "🔍 Vérifiez le statut avec:"
echo "   kubectl get pods -n local -l app=grafana"
echo "   kubectl logs -n local -l app=grafana"
