# Activer les APIs nécessaires
resource "google_project_service" "required_apis" {
  for_each = toset([
    "cloudresourcemanager.googleapis.com",
    "compute.googleapis.com",
    "run.googleapis.com",
    "sql-component.googleapis.com",
    "sqladmin.googleapis.com",
    "storage.googleapis.com",
    "secretmanager.googleapis.com",
    "artifactregistry.googleapis.com",
    "vpcaccess.googleapis.com",  # Pour VPC Access Connector
    "servicenetworking.googleapis.com"  # Pour Cloud SQL private network
  ])
  
  project = var.project_id
  service = each.value
  
  disable_on_destroy = true
}

# Génération d'un suffixe aléatoire pour l'unicité des noms
resource "random_string" "suffix" {
  length  = 6
  special = false
  upper   = false
}

# VPC Network pour les ressources
resource "google_compute_network" "projet_pro_vpc" {
  name                    = "projet-pro-vpc"
  auto_create_subnetworks = false
  # depends_on              = [google_project_service.required_apis]
}

# Subnet pour les services
resource "google_compute_subnetwork" "projet_pro_subnet" {
  name          = "projet-pro-subnet"
  ip_cidr_range = "10.0.1.0/24"
  region        = var.region
  network       = google_compute_network.projet_pro_vpc.id
  
  # Activer l'accès privé Google pour économiser les coûts de NAT
  private_ip_google_access = true
}

# Cloud SQL PostgreSQL instance (configuration économique)
resource "google_sql_database_instance" "postgres" {
  name             = "projet-pro-postgres-${random_string.suffix.result}"
  database_version = "POSTGRES_15"
  region           = var.region
  
  settings {
    # Configuration la moins chère
    tier              = "db-f1-micro"  # Le moins cher disponible
    availability_type = "ZONAL"       # Pas de haute disponibilité pour économiser
    disk_type         = "PD_HDD"      # HDD moins cher que SSD
    disk_size         = 10            # Taille minimale
    disk_autoresize   = true
    
    # Configuration de sauvegarde (minimale pour économiser)
    backup_configuration {
      enabled                        = true
      start_time                     = "03:00"  # Heure creuse
      point_in_time_recovery_enabled = false    # Désactivé pour économiser
      backup_retention_settings {
        retained_backups = 7                     # Minimum
        retention_unit   = "COUNT"
      }
    }
    
    # Configuration IP
    ip_configuration {
      ipv4_enabled    = false  # Pas d'IP publique pour la sécurité et économies
      private_network = google_compute_network.projet_pro_vpc.id
      # Pas d'authorized_networks car 10.0.0.0/8 est automatiquement inclus
    }
    
    # Pas de maintenance automatique pour éviter les interruptions
    maintenance_window {
      day          = 7    # Dimanche
      hour         = 3    # 3h du matin
      update_track = "stable"
    }
  }
  
  depends_on = [
    # google_project_service.required_apis,
    google_service_networking_connection.private_vpc_connection
  ]
  
  deletion_protection = false  # Pour pouvoir supprimer facilement en dev
}

# Configuration de la connexion privée pour Cloud SQL
resource "google_compute_global_address" "private_ip_address" {
  name          = "private-ip-address"
  purpose       = "VPC_PEERING"
  address_type  = "INTERNAL"
  prefix_length = 16
  network       = google_compute_network.projet_pro_vpc.id
}

resource "google_service_networking_connection" "private_vpc_connection" {
  network                 = google_compute_network.projet_pro_vpc.id
  service                 = "servicenetworking.googleapis.com"
  reserved_peering_ranges = [google_compute_global_address.private_ip_address.name]
}

# Base de données principale
resource "google_sql_database" "projetpro_db" {
  name     = "projetpro"
  instance = google_sql_database_instance.postgres.name
}

# Base de données pour Grafana
resource "google_sql_database" "grafana_db" {
  name     = "grafana"
  instance = google_sql_database_instance.postgres.name
}

# Utilisateur PostgreSQL
resource "google_sql_user" "postgres_user" {
  name     = "projetpro"
  instance = google_sql_database_instance.postgres.name
  password = var.postgres_password
}

# Cloud Storage buckets (configuration économique)
resource "google_storage_bucket" "machine_images" {
  name          = "projet-pro-machine-images-${random_string.suffix.result}"
  location      = var.region
  force_destroy = true
  
  # Configuration économique
  storage_class = "STANDARD"  # Le moins cher pour accès fréquent
  
  versioning {
    enabled = false  # Pas de versioning pour économiser
  }
  
  lifecycle_rule {
    condition {
      age = 90  # Supprimer après 90 jours
    }
    action {
      type = "Delete"
    }
  }
}

resource "google_storage_bucket" "temp_uploads" {
  name          = "projet-pro-temp-uploads-${random_string.suffix.result}"
  location      = var.region
  force_destroy = true
  
  storage_class = "STANDARD"
  
  versioning {
    enabled = false
  }
  
  lifecycle_rule {
    condition {
      age = 7  # Supprimer après 7 jours (temporaire)
    }
    action {
      type = "Delete"
    }
  }
}

resource "google_storage_bucket" "grafana_dashboards" {
  name          = "projet-pro-grafana-${random_string.suffix.result}"
  location      = var.region
  force_destroy = true
  
  storage_class = "NEARLINE"  # Moins cher pour accès occasionnel
  
  versioning {
    enabled = false
  }
}

# Secret Manager pour les variables sensibles
resource "google_secret_manager_secret" "jwt_secret" {
  secret_id = "jwt_secret"
  
  replication {
    user_managed {
      replicas {
        location = var.region
      }
    }
  }
  
  depends_on = [google_project_service.required_apis]
}

resource "google_secret_manager_secret_version" "jwt_secret_version" {
  secret      = google_secret_manager_secret.jwt_secret.id
  secret_data = var.jwt_secret
}

resource "google_secret_manager_secret" "postgres_password" {
  secret_id = "postgres_password"
  
  replication {
    user_managed {
      replicas {
        location = var.region
      }
    }
  }
  
  depends_on = [google_project_service.required_apis]
}

resource "google_secret_manager_secret_version" "postgres_password_version" {
  secret      = google_secret_manager_secret.postgres_password.id
  secret_data = var.postgres_password
}

resource "google_secret_manager_secret" "grafana_password" {
  secret_id = "grafana_admin_password"
  
  replication {
    user_managed {
      replicas {
        location = var.region
      }
    }
  }
  
  depends_on = [google_project_service.required_apis]
}

resource "google_secret_manager_secret_version" "grafana_password_version" {
  secret      = google_secret_manager_secret.grafana_password.id
  secret_data = var.grafana_admin_password
}

# Cloud Run Backend Service
resource "google_cloud_run_service" "backend" {
  name     = "backend-api"
  location = var.region
  
  template {
    metadata {
      annotations = {
        "autoscaling.knative.dev/minScale" = "0"  # Scale to zero pour économiser
        "autoscaling.knative.dev/maxScale" = "3"  # Limite pour contrôler les coûts
        "run.googleapis.com/vpc-access-connector" = google_vpc_access_connector.connector.name
        "run.googleapis.com/startup-timeout" = "300s"  # Timeout de démarrage étendu
        "run.googleapis.com/timeout" = "300s"  # Timeout de requête étendu
      }
    }
    
    spec {
      containers {
        image = "${var.dockerhub_user}/muscuscope:backend-0.0.4"
        
        # Configuration économique des ressources
        resources {
          limits = {
            cpu    = "1000m"     # 1 vCPU
            memory = "512Mi"     # 512MB
          }
          requests = {
            cpu    = "100m"      # 0.1 vCPU minimum
            memory = "128Mi"     # 128MB minimum
          }
        }
        
        ports {
          container_port = 80
        }
        
        env {
          name  = "APP_ENV"
          value = "prod"
        }
        
        env {
          name  = "APP_DEBUG"
          value = "false"
        }
        
        env {
          name  = "DATABASE_URL"
          value = "postgresql://${google_sql_user.postgres_user.name}:${var.postgres_password}@${google_sql_database_instance.postgres.private_ip_address}:5432/${google_sql_database.projetpro_db.name}?sslmode=require"
        }
        
        env {
          name  = "DATABASE_HOST"
          value = google_sql_database_instance.postgres.private_ip_address
        }
        
        env {
          name  = "DATABASE_NAME"
          value = google_sql_database.projetpro_db.name
        }
        
        env {
          name  = "DATABASE_USER"
          value = google_sql_user.postgres_user.name
        }
        
        env {
          name = "DATABASE_PASSWORD"
          value_from {
            secret_key_ref {
              name = "postgres_password"
              key  = "latest"
            }
          }
        }
        
        env {
          name = "JWT_SECRET"
          value_from {
            secret_key_ref {
              name = "jwt_secret"
              key  = "latest"
            }
          }
        }
        
        env {
          name  = "GCS_BUCKET_IMAGES"
          value = google_storage_bucket.machine_images.name
        }
        
        env {
          name  = "GCS_BUCKET_TEMP"
          value = google_storage_bucket.temp_uploads.name
        }
        
        env {
          name  = "CORS_ALLOW_ORIGIN"
          value = "*"
        }
      }
    }
  }
  
  traffic {
    percent         = 100
    latest_revision = true
  }
  
  depends_on = [
    # google_project_service.required_apis,
    google_secret_manager_secret_iam_binding.jwt_secret_accessor,
    google_secret_manager_secret_iam_binding.postgres_password_accessor,
    google_storage_bucket_iam_binding.machine_images_object_admin,
    google_storage_bucket_iam_binding.temp_uploads_object_admin
  ]
}

# Cloud Run Frontend Service
resource "google_cloud_run_service" "frontend" {
  name     = "frontend"
  location = var.region
  
  template {
    metadata {
      annotations = {
        "autoscaling.knative.dev/minScale" = "0"  # Scale to zero
        "autoscaling.knative.dev/maxScale" = "3"
      }
    }
    
    spec {
      containers {
        image = "${var.dockerhub_user}/muscuscope:frontend-0.0.4"
        
        resources {
          limits = {
            cpu    = "1000m"
            memory = "256Mi"     # Frontend nécessite moins de mémoire
          }
          requests = {
            cpu    = "100m"
            memory = "64Mi"
          }
        }
        
        ports {
          container_port = 80
        }
        
        env {
          name  = "VITE_APP_NAME"
          value = "MuscuScope"
        }
        
        env {
          name  = "VITE_ENVIRONMENT"
          value = var.environment
        }

        env {
          name  = "VITE_API_URL"
          value = google_cloud_run_service.backend.traffic[0].url
        }
      }
    }
  }
  
  traffic {
    percent         = 100
    latest_revision = true
  }
  
  depends_on = [google_cloud_run_service.backend]
}

# Cloud Run Grafana Service
resource "google_cloud_run_service" "grafana" {
  name     = "grafana"
  location = var.region
  
  template {
    metadata {
      annotations = {
        "autoscaling.knative.dev/minScale" = "0"
        "autoscaling.knative.dev/maxScale" = "2"
        "run.googleapis.com/vpc-access-connector" = google_vpc_access_connector.connector.name
      }
    }
    
    spec {
      containers {
        image = "grafana/grafana-oss:12.0.2-ubuntu"
        
        resources {
          limits = {
            cpu    = "500m"      # Moins de CPU pour Grafana
            memory = "256Mi"
          }
          requests = {
            cpu    = "100m"
            memory = "128Mi"
          }
        }
        
        ports {
          container_port = 3000
        }
        
        env {
          name  = "GF_SERVER_HTTP_PORT"
          value = "3000"
        }
        
        env {
          name  = "GF_SECURITY_ADMIN_USER"
          value = "admin"
        }
        
        env {
          name = "GF_SECURITY_ADMIN_PASSWORD"
          value_from {
            secret_key_ref {
              name = "grafana_admin_password"
              key  = "latest"
            }
          }
        }
        
        env {
          name  = "GF_DATABASE_TYPE"
          value = "postgres"
        }
        
        env {
          name  = "GF_DATABASE_HOST"
          value = "${google_sql_database_instance.postgres.private_ip_address}:5432"
        }
        
        env {
          name  = "GF_DATABASE_NAME"
          value = google_sql_database.grafana_db.name
        }
        
        env {
          name  = "GF_DATABASE_USER"
          value = google_sql_user.postgres_user.name
        }
        
        env {
          name = "GF_DATABASE_PASSWORD"
          value_from {
            secret_key_ref {
              name = "postgres_password"
              key  = "latest"
            }
          }
        }
        
        env {
          name  = "GF_DATABASE_SSL_MODE"
          value = "require"
        }
        
        # Optimisations pour économiser les ressources
        env {
          name  = "GF_ANALYTICS_REPORTING_ENABLED"
          value = "false"
        }
        
        env {
          name  = "GF_ANALYTICS_CHECK_FOR_UPDATES"
          value = "false"
        }
        
        env {
          name  = "GF_ALERTING_ENABLED"
          value = "false"
        }
      }
    }
  }
  
  traffic {
    percent         = 100
    latest_revision = true
  }
  
  depends_on = [
    # google_project_service.required_apis,
    google_secret_manager_secret_iam_binding.grafana_password_accessor,
    google_secret_manager_secret_iam_binding.postgres_password_accessor,
    google_storage_bucket_iam_binding.grafana_dashboards_object_admin
  ]
}

# VPC Access Connector pour Cloud Run accéder au VPC
resource "google_vpc_access_connector" "connector" {
  name          = "projet-pro-connector-v2"  # Nouveau nom pour éviter le conflit
  ip_cidr_range = "10.8.0.0/28"
  network       = google_compute_network.projet_pro_vpc.name
  region        = var.region
  
  # Configuration économique avec instances minimales
  min_instances = 2
  max_instances = 3  # Limite pour contrôler les coûts
  
  depends_on = [google_project_service.required_apis]
}

# IAM bindings pour Cloud Run invoker (public access)
resource "google_cloud_run_service_iam_binding" "backend_invoker" {
  service  = google_cloud_run_service.backend.name
  location = google_cloud_run_service.backend.location
  role     = "roles/run.invoker"
  members  = ["allUsers"]
}

resource "google_cloud_run_service_iam_binding" "frontend_invoker" {
  service  = google_cloud_run_service.frontend.name
  location = google_cloud_run_service.frontend.location
  role     = "roles/run.invoker"
  members  = ["allUsers"]
}

resource "google_cloud_run_service_iam_binding" "grafana_invoker" {
  service  = google_cloud_run_service.grafana.name
  location = google_cloud_run_service.grafana.location
  role     = "roles/run.invoker"
  members  = ["allUsers"]
}

# Permissions IAM pour accéder aux secrets depuis Cloud Run
# Service account utilisé par Cloud Run par défaut (numéro de projet connu)
locals {
  compute_service_account = "470976636166-compute@developer.gserviceaccount.com"
}

# Permission pour accéder au secret JWT
resource "google_secret_manager_secret_iam_binding" "jwt_secret_accessor" {
  secret_id = google_secret_manager_secret.jwt_secret.secret_id
  role      = "roles/secretmanager.secretAccessor"
  members = [
    "serviceAccount:${local.compute_service_account}"
  ]
}

# Permission pour accéder au secret password PostgreSQL
resource "google_secret_manager_secret_iam_binding" "postgres_password_accessor" {
  secret_id = google_secret_manager_secret.postgres_password.secret_id
  role      = "roles/secretmanager.secretAccessor"
  members = [
    "serviceAccount:${local.compute_service_account}"
  ]
}

# Permission pour accéder au secret password Grafana
resource "google_secret_manager_secret_iam_binding" "grafana_password_accessor" {
  secret_id = google_secret_manager_secret.grafana_password.secret_id
  role      = "roles/secretmanager.secretAccessor"
  members = [
    "serviceAccount:${local.compute_service_account}"
  ]
}

# Permissions pour Cloud Storage (si nécessaire)
resource "google_storage_bucket_iam_binding" "machine_images_object_admin" {
  bucket = google_storage_bucket.machine_images.name
  role   = "roles/storage.objectAdmin"
  members = [
    "serviceAccount:${local.compute_service_account}"
  ]
}

resource "google_storage_bucket_iam_binding" "temp_uploads_object_admin" {
  bucket = google_storage_bucket.temp_uploads.name
  role   = "roles/storage.objectAdmin"
  members = [
    "serviceAccount:${local.compute_service_account}"
  ]
}

resource "google_storage_bucket_iam_binding" "grafana_dashboards_object_admin" {
  bucket = google_storage_bucket.grafana_dashboards.name
  role   = "roles/storage.objectAdmin"
  members = [
    "serviceAccount:${local.compute_service_account}"
  ]
}
