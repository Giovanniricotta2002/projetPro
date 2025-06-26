
output "postgres_fqdn" {
  description = "FQDN du serveur PostgreSQL"
  value       = azurerm_postgresql_flexible_server.pg.fqdn
}