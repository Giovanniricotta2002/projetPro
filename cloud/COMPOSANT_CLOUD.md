# Composants Cloud - MuscuScope

## Infrastructure de Production (GCP Cloud Run)

- **Backend API** => Google Cloud Run (conteneur Docker Symfony)
- **Frontend Web** => Google Cloud Run (conteneur Docker Vue.js/Nginx)
- **Base de données** => Cloud SQL PostgreSQL (db-f1-micro, économique)
- **Stockage fichiers** => Google Cloud Storage (machine images, uploads temporaires)
- **Monitoring** => Cloud Run (Grafana + PostgreSQL)
- **Secrets** => Secret Manager (JWT, mots de passe)
- **Réseau** => VPC privé + VPC Access Connector
- **DNS/Load Balancing** => Cloud Run (URLs automatiques)

## ?

- **URLs actuelles** :
  - Frontend: `https://frontend-470976636166.europe-west1.run.app`
  - Backend: `https://backend-api-66g7tud2sq-ew.a.run.app`
  - Grafana: `https://grafana-[hash].europe-west1.run.app`

## Déploiement

- **Infrastructure as Code** : Terraform GCP (`docker/terraform-gcp/`)
- **Images Docker** : Docker Hub (`giovanni2002ynov/muscuscope:*`)
- **CI/CD** : Déploiement manuel via gcloud (Terraform auth. issues)
