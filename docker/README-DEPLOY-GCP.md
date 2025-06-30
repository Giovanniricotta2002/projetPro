# Guide de déploiement GCP - MuscuScope

Ce guide explique comment déployer MuscuScope sur Google Cloud Platform avec la résolution correcte du problème CORS.

## Problème résolu

**Problème initial** : Le frontend utilisait une URL statique `https://api.muscuscope.local` au lieu de l'URL dynamique du backend Cloud Run `https://backend-api-470976636166.europe-west1.run.app`.

**Solution** : Déploiement en deux phases avec build dynamique des images Docker.

## Architecture de la solution

```
Phase 1: Backend
├── Build backend avec CORS wildcard
├── Deploy backend sur Cloud Run
└── Récupération de l'URL backend

Phase 2: Frontend  
├── Build frontend avec URL backend réelle
├── Deploy frontend sur Cloud Run
└── Configuration CORS complète
```

## Prérequis

1. **Google Cloud CLI installé et configuré**
   ```bash
   # Installation (Ubuntu/Debian)
   curl https://sdk.cloud.google.com | bash
   exec -l $SHELL
   gcloud init
   
   # Connexion
   gcloud auth login
   gcloud auth application-default login
   ```

2. **Docker Hub account et connexion**
   ```bash
   docker login
   ```

3. **Terraform installé**
   ```bash
   terraform --version
   ```

4. **Configuration du projet GCP**
   - Projet GCP créé avec facturation activée
   - APIs Cloud Resource Manager et Compute Engine activées
   - ID de projet noté

## Configuration

1. **Modifier le fichier terraform.tfvars**
   ```bash
   cd /home/gio/projetPro/docker/terraform-gcp
   vi terraform.tfvars
   ```
   
   Ajuster au minimum :
   ```terraform
   project_id = "votre-projet-id"
   dockerhub_user = "votre-dockerhub-user"
   ```

## Déploiement automatique (Recommandé)

Utiliser le script automatisé qui gère les deux phases :

```bash
cd /home/gio/projetPro/docker
./deploy-gcp.sh [PROJECT_ID] [REGION] [DOCKERHUB_USER] [ENVIRONMENT]
```

Exemple :
```bash
./deploy-gcp.sh automatic-rite-464507-d7 europe-west1 giovanni2002ynov prod
```

Le script va :
1. ✅ Build et deploy le backend avec CORS wildcard
2. ✅ Récupérer l'URL du backend Cloud Run
3. ✅ Build le frontend avec cette URL intégrée au build Vite
4. ✅ Deploy le frontend avec la configuration correcte
5. ✅ Afficher les URLs finales

## Déploiement manuel

Si vous préférez déployer manuellement :

### Phase 1: Backend

```bash
# Build backend avec CORS
cd /home/gio/projetPro/back
docker build --build-arg CORS_ALLOW_ORIGIN="https://*-run.app" \
  -t giovanni2002ynov/muscuscope:backend-latest .
docker push giovanni2002ynov/muscuscope:backend-latest

# Deploy infrastructure backend
cd /home/gio/projetPro/docker/terraform-gcp
terraform init
terraform apply -target=google_cloud_run_service.backend -auto-approve

# Récupérer URL backend
BACKEND_URL=$(terraform output -raw backend_url)
echo "Backend URL: $BACKEND_URL"
```

### Phase 2: Frontend

```bash
# Build frontend avec URL backend
cd /home/gio/projetPro/front
docker build --build-arg VITE_API_URL="$BACKEND_URL" \
  -t giovanni2002ynov/muscuscope:frontend-latest .
docker push giovanni2002ynov/muscuscope:frontend-latest

# Deploy infrastructure complète
cd /home/gio/projetPro/docker/terraform-gcp
terraform apply -auto-approve
```

## Vérification

Après déploiement, vérifier :

1. **Backend Health Check**
   ```bash
   curl https://backend-api-XXXXX.europe-west1.run.app/api/health
   ```

2. **CSRF Token (test CORS)**
   ```bash
   curl https://backend-api-XXXXX.europe-west1.run.app/api/csrfToken
   ```

3. **Frontend accessible**
   - Ouvrir l'URL frontend dans le navigateur
   - Vérifier que les appels API se font vers la bonne URL backend

## Configuration CORS expliquée

### Backend (Symfony)
- Variable `CORS_ALLOW_ORIGIN` configurée avec wildcard `https://*-run.app`
- Accepte tous les domaines Cloud Run (frontend et autres)

### Frontend (Vue.js/Vite)
- `VITE_API_URL` définie au build time avec l'URL réelle du backend
- Pas de variable runtime, l'URL est compilée dans le bundle

### Flux CORS résolu
```
Frontend (https://frontend-XXXXX.run.app)
   ↓ compile avec VITE_API_URL
   ↓ appels vers https://backend-api-XXXXX.run.app
   ↓ Backend accepte car CORS_ALLOW_ORIGIN=https://*-run.app
   ✅ CORS OK
```

## Coûts estimés

- **PostgreSQL** (db-f1-micro) : ~15€/mois
- **Cloud Run** (scale-to-zero) : ~5-20€/mois selon usage
- **Cloud Storage** : ~2-5€/mois
- **Networking** : ~3-10€/mois
- **Total** : ~25-50€/mois

## Nettoyage

Pour supprimer toute l'infrastructure :
```bash
cd /home/gio/projetPro/docker/terraform-gcp
terraform destroy -auto-approve
```

## Troubleshooting

### Erreur CORS persistante
Vérifier que l'image frontend a été buildée avec la bonne `VITE_API_URL` :
```bash
# Vérifier le contenu de l'image
docker run --rm giovanni2002ynov/muscuscope:frontend-latest cat /usr/share/nginx/html/assets/index-*.js | grep -o 'https://[^"]*api'
```

### Erreur 503 Backend
Vérifier les logs Cloud Run :
```bash
gcloud logs read --service=backend-api --region=europe-west1
```

### Timeout Terraform
Augmenter les timeouts ou appliquer par étapes avec `--target`.
