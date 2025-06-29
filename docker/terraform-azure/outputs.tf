# Outputs pour récupérer les informations importantes
output "resource_group_name" {
  description = "Nom du groupe de ressources"
  value       = azurerm_resource_group.rg.name
}

output "storage_account_name" {
  description = "Nom du compte de stockage Azure"
  value       = azurerm_storage_account.projet_pro.name
}

output "storage_account_primary_endpoint" {
  description = "Endpoint principal du stockage Blob"
  value       = azurerm_storage_account.projet_pro.primary_blob_endpoint
}

output "storage_connection_string" {
  description = "Chaîne de connexion du stockage (sensible)"
  value       = azurerm_storage_account.projet_pro.primary_connection_string
  sensitive   = true
}

output "database_fqdn" {
  description = "FQDN de la base de données PostgreSQL"
  value       = azurerm_postgresql_flexible_server.projet_pro.fqdn
}

output "backend_url" {
  description = "URL du backend (Container App)"
  value       = "https://${azurerm_container_app.backend-api.latest_revision_fqdn}"
}

output "frontend_url" {
  description = "URL du frontend (Container App)"
  value       = "https://${azurerm_container_app.front-end.latest_revision_fqdn}"
}

output "blob_containers" {
  description = "Liste des containers blob créés"
  value = {
    machine_images = azurerm_storage_container.machine_images.name
    temp_uploads   = azurerm_storage_container.temp_uploads.name
    public_assets  = azurerm_storage_container.public_assets.name
  }
}

# Estimation des coûts (approximative)
output "estimated_monthly_cost_info" {
  description = "Information sur les coûts estimés mensuels (approximatif)"
  value = {
    storage_account = "~5-15€/mois (selon utilisation)"
    postgresql_server = "~30-50€/mois (B_Standard_B4ms)"
    container_apps = "~20-40€/mois (selon trafic)"
    network_costs = "~2-5€/mois"
    total_estimated = "~57-110€/mois"
    note = "Coûts réels dépendent de l'utilisation réelle"
  }
}