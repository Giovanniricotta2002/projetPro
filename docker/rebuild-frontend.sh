#!/bin/bash

# Script pour rebuilder et redéployer le frontend avec la bonne URL backend

set -e

echo "🔄 Rebuild et redéploiement du frontend avec l'URL backend correcte"

# Récupération dynamique de l'URL du backend
echo "🔍 Récupération de l'URL du backend Cloud Run..."
BACKEND_URL=$(gcloud run services describe backend-api --region=europe-west1 --format="value(status.url)")

if [ -z "$BACKEND_URL" ]; then
    echo "❌ Impossible de récupérer l'URL du backend"
    exit 1
fi

# Configuration
FRONTEND_TAG="frontend-0.0.5"  # Nouvelle version
DOCKERHUB_USER="giovanni2002ynov"

echo "📋 Configuration:"
echo "   Backend URL: $BACKEND_URL"
echo "   Frontend Tag: $FRONTEND_TAG"
echo "   Docker Hub User: $DOCKERHUB_USER"

# Vérifier que le backend répond
echo "🔍 Vérification que le backend répond..."
if curl -sf "$BACKEND_URL/api/csrfToken" >/dev/null; then
    echo "✅ Backend accessible"
else
    echo "❌ Backend non accessible"
    exit 1
fi

# Build du frontend avec la bonne URL backend
echo "📦 Build de l'image frontend avec URL backend: $BACKEND_URL"
cd /home/gio/projetPro/front

docker build \
    --build-arg VITE_API_URL="$BACKEND_URL" \
    --build-arg VITE_APP_NAME="MuscuScope" \
    -t "$DOCKERHUB_USER/muscuscope:$FRONTEND_TAG" \
    .

# Vérifier que l'image a été créée
echo "🔍 Vérification de l'image buildée..."
if docker images | grep -q "$DOCKERHUB_USER/muscuscope.*$FRONTEND_TAG"; then
    echo "✅ Image buildée avec succès"
else
    echo "❌ Échec du build de l'image"
    exit 1
fi

# Push de l'image
echo "⬆️ Push de l'image vers Docker Hub..."
docker push "$DOCKERHUB_USER/muscuscope:$FRONTEND_TAG"

# Déploiement sur Cloud Run
echo "🚀 Déploiement sur Cloud Run..."
gcloud run services update frontend \
    --image="$DOCKERHUB_USER/muscuscope:$FRONTEND_TAG" \
    --region=europe-west1

# Récupérer l'URL finale du frontend
FRONTEND_URL=$(gcloud run services describe frontend --region=europe-west1 --format="value(status.url)")

echo ""
echo "🎉 Déploiement terminé avec succès!"
echo ""
echo "📱 URLs de l'application:"
echo "   Frontend: $FRONTEND_URL"
echo "   Backend:  $BACKEND_URL"
echo ""
echo "🧪 Test du problème CORS résolu:"
echo "   1. Ouvrir: $FRONTEND_URL"
echo "   2. Ouvrir les DevTools du navigateur (F12)"
echo "   3. Onglet Network/Réseau"
echo "   4. Effectuer une action qui appelle l'API"
echo "   5. Vérifier que les appels vont vers $BACKEND_URL"
echo ""
echo "🔧 Test API direct:"
echo "   curl $BACKEND_URL/api/csrfToken"