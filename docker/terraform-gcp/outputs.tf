# Outputs pour récupérer les informations importantes
output "project_id" {
  description = "ID du projet Google Cloud"
  value       = var.project_id
}

output "region" {
  description = "Région GCP utilisée"
  value       = var.region
}

output "database_connection_name" {
  description = "Nom de connexion de la base de données"
  value       = google_sql_database_instance.postgres.connection_name
}

output "database_private_ip" {
  description = "IP privée de la base de données"
  value       = google_sql_database_instance.postgres.private_ip_address
  sensitive   = true
}

output "backend_url" {
  description = "URL du backend (Cloud Run)"
  value       = google_cloud_run_service.backend.status[0].url
}

output "frontend_url" {
  description = "URL du frontend (Cloud Run)"
  value       = google_cloud_run_service.frontend.status[0].url
}

output "grafana_url" {
  description = "URL de Grafana (Cloud Run)"
  value       = google_cloud_run_service.grafana.status[0].url
}

output "storage_buckets" {
  description = "Liste des buckets Cloud Storage créés"
  value = {
    machine_images     = google_storage_bucket.machine_images.name
    temp_uploads       = google_storage_bucket.temp_uploads.name
    grafana_dashboards = google_storage_bucket.grafana_dashboards.name
  }
}

output "vpc_network_name" {
  description = "Nom du réseau VPC"
  value       = google_compute_network.projet_pro_vpc.name
}

output "secret_manager_secrets" {
  description = "Liste des secrets créés dans Secret Manager"
  value = {
    jwt_secret       = google_secret_manager_secret.jwt_secret.name
    postgres_password = google_secret_manager_secret.postgres_password.name
    grafana_password = google_secret_manager_secret.grafana_password.name
  }
}

# Estimation des coûts (approximative pour GCP)
output "estimated_monthly_cost_info" {
  description = "Information sur les coûts estimés mensuels GCP (approximatif)"
  value = {
    cloud_sql_postgres = "~15-25€/mois (db-f1-micro)"
    cloud_run_services = "~10-30€/mois (selon trafic, scale-to-zero)"
    cloud_storage = "~2-8€/mois (selon utilisation)"
    networking = "~2-5€/mois (VPC, NAT)"
    secret_manager = "~1€/mois"
    total_estimated = "~30-70€/mois"
    note = "GCP généralement 30-40% moins cher qu'Azure. Coûts réels selon utilisation."
    optimization = "Scale-to-zero activé sur Cloud Run pour économies maximales"
  }
}

# URLs spécifiques pour les tests API
output "api_health_url" {
  description = "URL pour tester l'API de santé"
  value       = "${google_cloud_run_service.backend.status[0].url}/api/health"
}

output "csrf_token_url" {
  description = "URL pour récupérer le token CSRF"
  value       = "${google_cloud_run_service.backend.status[0].url}/api/csrfToken"
}

# Instructions de déploiement
output "deployment_instructions" {
  description = "Instructions pour le déploiement"
  value = {
    step_1 = "Créer un projet GCP et activer la facturation"
    step_2 = "Installer gcloud CLI et se connecter: gcloud auth application-default login"
    step_3 = "Définir PROJECT_ID dans terraform.tfvars"
    step_4 = "Exécuter: terraform init && terraform plan && terraform apply"
    step_5 = "Les URLs des services seront affichées dans les outputs"
    documentation = "https://cloud.google.com/docs/terraform"
  }
}
