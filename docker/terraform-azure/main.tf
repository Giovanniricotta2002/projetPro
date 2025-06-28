resource "azurerm_resource_group" "rg" {
  name     = var.resource_group_name
  location = var.location
}

resource "azurerm_virtual_network" "projet_pro" {
  name = "projet-pro-vn"
  location = azuezrm_resource_group.rg.location
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
      image = "docker.io/${var.dockerhub_user}/backend-api:0.0.2"
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
    }
  }

  depends_on = [ azurerm_container_app_environment.projetc_pro_env ]

  ingress {
    external_enabled = true
    target_port = 3000
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
      image = "docker.io/${var.dockerhub_user}/front-end:0.0.2"
      cpu = "0.5"
      memory = "1.0Gi"

      env {
        name = "VITE_BACKEND_URL"
        value = "https://${azurerm_container_app.backend-api.fqdn}"
      }
    }
  }

  ingress {
    external_enabled = true
    target_port = 3080
    traffic_weight {
      latest_revision = true
      percentage = 100
    }
  }
}