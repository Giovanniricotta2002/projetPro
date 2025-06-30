resource "azurerm_resource_group" "rg" {
  name     = var.resource_group_name
  location = var.location
}

resource "azurerm_virtual_network" "projet_pro" {
  name = "projet-pro-vn"
  location = azurerm_resource_group.rg.location
  resource_group_name = azurerm_resource_group.rg.name
  address_space = ["10.0.0.0/16"]
}

resource "azurerm_subnet" "projet_pro_psql_subnet" {
  name = "psql-subnet"
  resource_group_name = azurerm_resource_group.rg.name
  virtual_network_name = azurerm_virtual_network.projet_pro.name
  address_prefixes = ["10.0.2.0/24"]

  delegation {
    name = "psql-delegation"

    service_delegation {
      name = "Microsoft.DBforPostgreSQL/flexibleServers"
      actions = [
        "Microsoft.Network/virtualNetworks/subnets/join/action",
      ]
    }
  }

  private_endpoint_network_policies = "Disabled"
  private_link_service_network_policies_enabled = true
}

resource "azurerm_subnet" "projet_pro_aca_subnet" {
  name = "aca-subnet"
  resource_group_name = azurerm_resource_group.rg.name
  virtual_network_name = azurerm_virtual_network.projet_pro.name
  address_prefixes = ["10.0.20.0/23"]

  private_endpoint_network_policies = "Disabled"
  private_link_service_network_policies_enabled = true
}

resource "azurerm_private_dns_zone" "projet_pro" {
  name = "projet_pro.postgres.database.azure.com"
  resource_group_name = azurerm_resource_group.rg.name
}

resource "azurerm_private_dns_zone_virtual_network_link" "projet_pro" {
  name = "projetProVnetZone.com"
  private_dns_zone_name = azurerm_private_dns_zone.projet_pro.name
  virtual_network_id = azurerm_virtual_network.projet_pro.id
  resource_group_name = azurerm_resource_group.rg.name
  depends_on = [ azurerm_subnet.projet_pro_psql_subnet ]
}

resource "azurerm_postgresql_flexible_server" "projet_pro" {
  name = "projet-pro-psqlflexibleserver"
  resource_group_name = azurerm_resource_group.rg.name
  location = azurerm_resource_group.rg.location
  version = "16"
  delegated_subnet_id = azurerm_subnet.projet_pro_psql_subnet.id
  private_dns_zone_id = azurerm_private_dns_zone.projet_pro.id
  public_network_access_enabled = false
  administrator_login = "psqladmin"
  administrator_password = var.postgres_password
  zone = "1"

  storage_mb = "32768"
  storage_tier = "P4"

  sku_name = "B_Standard_B4ms"
  depends_on = [ azurerm_private_dns_zone_virtual_network_link.projet_pro ]
}

resource "azurerm_container_app_environment" "projetc_pro_env" {
  name = "aca-projet-pro-env"
  location = azurerm_resource_group.rg.location
  resource_group_name = azurerm_resource_group.rg.name
  infrastructure_subnet_id = azurerm_subnet.projet_pro_aca_subnet.id
}

resource "azurerm_container_app" "backend-api" {
  name = "backend-api"
  resource_group_name = azurerm_resource_group.rg.name
  container_app_environment_id = azurerm_container_app_environment.projetc_pro_env.id
  revision_mode = "Single"
  max_inactive_revisions = 0

  template {
    min_replicas = 1
    max_replicas = 5

    container {
      name = "backend-api"
      image = "docker.io/${var.dockerhub_user}/muscuscope:backend"
      cpu = "0.5"
      memory = "1.0Gi"

      env {
        name = "DATABASE_HOST"
        value = azurerm_postgresql_flexible_server.projet_pro.fqdn
      }

      env {
        name = "DATABASE_USERNAME"
        value = "psqladmin"
      }

      env {
        name = "DATABASE_PASSWORD"
        value = var.postgres_password
      }

      env {
        name = "DATABASE_SSL"
        value = "true"
      }

      env {
        name = "JWT_SECRET"
        value = var.jwt_secret
      }

      env {
        name = "AZURE_STORAGE_ACCOUNT_NAME"
        value = azurerm_storage_account.projet_pro.name
      }

      env {
        name = "AZURE_STORAGE_ACCOUNT_KEY"
        value = azurerm_storage_account.projet_pro.primary_access_key
      }

      env {
        name = "AZURE_STORAGE_CONNECTION_STRING"
        value = azurerm_storage_account.projet_pro.primary_connection_string
      }

      env {
        name = "AZURE_BLOB_CONTAINER_IMAGES"
        value = azurerm_storage_container.machine_images.name
      }
    }
  }

  depends_on = [ azurerm_container_app_environment.projetc_pro_env ]

  ingress {
    external_enabled = true
    target_port = 80  # Port du backend (Apache)
    traffic_weight {
      latest_revision = true
      percentage = 100
    }
  }
}

resource "azurerm_container_app" "front-end" {
  name = "front-end"
  resource_group_name = azurerm_resource_group.rg.name
  container_app_environment_id = azurerm_container_app_environment.projetc_pro_env.id
  revision_mode = "Single"

  template {
    min_replicas = 1
    max_replicas = 5

    container {
      name = "front-end"
      image = "docker.io/${var.dockerhub_user}/muscuscope:frontend"
      cpu = "0.5"
      memory = "1.0Gi"

      env {
        name = "VITE_API_URL"
        value = "https://backend-api.${azurerm_container_app_environment.projetc_pro_env.default_domain}"
      }

      env {
        name = "VITE_APP_NAME"
        value = "MuscuScope"
      }

      env {
        name = "VITE_ENVIRONMENT"
        value = "production"
      }
    }
  }

  ingress {
    external_enabled = true
    target_port = 80  # Port du frontend (Nginx)
    traffic_weight {
      latest_revision = true
      percentage = 100
    }
  }

  depends_on = [azurerm_container_app.backend-api]
}

# Azure Storage Account pour Blob Storage (configuration économique)
resource "azurerm_storage_account" "projet_pro" {
  name                     = "stprojetpro${random_string.storage_suffix.result}"
  resource_group_name      = azurerm_resource_group.rg.name
  location                 = azurerm_resource_group.rg.location
  
  # Configuration la moins chère
  account_tier             = "Standard"        # Standard (moins cher que Premium)
  account_replication_type = "LRS"            # Locally Redundant Storage (le moins cher)
  account_kind            = "StorageV2"       # StorageV2 pour les fonctionnalités modernes
  
  # Optimisations de coût
  access_tier             = "Cool"            # Cool tier (moins cher pour accès occasionnel)
  min_tls_version         = "TLS1_2"
  
  # Sécurité
  https_traffic_only_enabled = true
  allow_nested_items_to_be_public = false
  
  # Pas de versioning par défaut (économise l'espace)
  blob_properties {
    versioning_enabled = false
    delete_retention_policy {
      days = 7  # Rétention minimale (7 jours)
    }
    container_delete_retention_policy {
      days = 7  # Rétention minimale (7 jours)
    }
  }

  tags = {
    Environment = "production"
    Project     = "MuscuScope"
    CostCenter  = "development"
  }
}

# Génération d'un suffixe aléatoire pour l'unicité du nom du storage
resource "random_string" "storage_suffix" {
  length  = 6
  special = false
  upper   = false
}

# Container pour les images des machines de musculation
resource "azurerm_storage_container" "machine_images" {
  name                  = "machine-images"
  storage_account_id    = azurerm_storage_account.projet_pro.id
  container_access_type = "private"  # Accès privé pour la sécurité
}

# Container pour les uploads temporaires
resource "azurerm_storage_container" "temp_uploads" {
  name                  = "temp-uploads"
  storage_account_id    = azurerm_storage_account.projet_pro.id
  container_access_type = "private"
}

# Container pour les assets publics (si nécessaire)
resource "azurerm_storage_container" "public_assets" {
  name                  = "public-assets"
  storage_account_id    = azurerm_storage_account.projet_pro.id
  container_access_type = "blob"  # Accès public en lecture seule
}

# Container pour les dashboards Grafana (sauvegarde)
resource "azurerm_storage_container" "grafana_dashboards" {
  name                  = "grafana-dashboards"
  storage_account_id    = azurerm_storage_account.projet_pro.id
  container_access_type = "private"
}

# Container App pour Grafana
resource "azurerm_container_app" "grafana" {
  name = "grafana"
  resource_group_name = azurerm_resource_group.rg.name
  container_app_environment_id = azurerm_container_app_environment.projetc_pro_env.id
  revision_mode = "Single"
  max_inactive_revisions = 0

  template {
    min_replicas = 1
    max_replicas = 2  # Limite pour réduire les coûts

    container {
      name = "grafana"
      image = "grafana/grafana-oss:12.0.2-ubuntu"  # Version open source gratuite
      cpu = "0.25"    # CPU minimal pour réduire les coûts
      memory = "0.5Gi" # Mémoire minimale

      # Configuration Grafana via variables d'environnement
      env {
        name = "GF_SERVER_HTTP_PORT"
        value = "3000"
      }

      env {
        name = "GF_SERVER_DOMAIN"
        value = "grafana.${azurerm_container_app_environment.projetc_pro_env.default_domain}"
      }

      env {
        name = "GF_SERVER_ROOT_URL"
        value = "https://grafana.${azurerm_container_app_environment.projetc_pro_env.default_domain}"
      }

      env {
        name = "GF_SECURITY_ADMIN_USER"
        value = "admin"
      }

      env {
        name = "GF_SECURITY_ADMIN_PASSWORD"
        value = var.grafana_admin_password
      }

      env {
        name = "GF_AUTH_ANONYMOUS_ENABLED"
        value = "false"
      }

      env {
        name = "GF_SECURITY_ALLOW_EMBEDDING"
        value = "true"
      }

      # Configuration de la base de données PostgreSQL pour Grafana
      env {
        name = "GF_DATABASE_TYPE"
        value = "postgres"
      }

      env {
        name = "GF_DATABASE_HOST"
        value = "${azurerm_postgresql_flexible_server.projet_pro.fqdn}:5432"
      }

      env {
        name = "GF_DATABASE_NAME"
        value = "grafana"
      }

      env {
        name = "GF_DATABASE_USER"
        value = "psqladmin"
      }

      env {
        name = "GF_DATABASE_PASSWORD"
        value = var.postgres_password
      }

      env {
        name = "GF_DATABASE_SSL_MODE"
        value = "require"
      }

      # Désactiver les télémétries pour économiser la bande passante
      env {
        name = "GF_ANALYTICS_REPORTING_ENABLED"
        value = "false"
      }

      env {
        name = "GF_ANALYTICS_CHECK_FOR_UPDATES"
        value = "false"
      }

      # Configuration pour économiser les ressources
      env {
        name = "GF_ALERTING_ENABLED"
        value = "false"  # Désactiver les alertes pour économiser les ressources
      }

      env {
        name = "GF_EXPLORE_ENABLED"
        value = "true"
      }

      # Configuration du stockage Azure pour les snapshots (optionnel)
      env {
        name = "GF_SNAPSHOTS_EXTERNAL_ENABLED"
        value = "true"
      }

      env {
        name = "GF_EXTERNAL_IMAGE_STORAGE_PROVIDER"
        value = "azure_blob"
      }

      env {
        name = "GF_EXTERNAL_IMAGE_STORAGE_AZURE_BLOB_ACCOUNT_NAME"
        value = azurerm_storage_account.projet_pro.name
      }

      env {
        name = "GF_EXTERNAL_IMAGE_STORAGE_AZURE_BLOB_ACCOUNT_KEY"
        value = azurerm_storage_account.projet_pro.primary_access_key
      }

      env {
        name = "GF_EXTERNAL_IMAGE_STORAGE_AZURE_BLOB_CONTAINER_NAME"
        value = azurerm_storage_container.grafana_dashboards.name
      }
    }
  }

  depends_on = [ 
    azurerm_container_app_environment.projetc_pro_env,
    azurerm_postgresql_flexible_server.projet_pro
  ]

  ingress {
    external_enabled = true
    target_port = 3000
    traffic_weight {
      latest_revision = true
      percentage = 100
    }
  }
}