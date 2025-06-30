variable "resource_group_name" {
  default = "rg-projet-pro"
}

variable "location" {
  default = "swedencentral"
}

variable "dockerhub_user" {
  default = "gio"
}

variable "jwt_secret" {
  type = string
}

variable "postgres_password" {
  description = "PostgreSQL admin password"
  sensitive = true
}

variable "grafana_admin_password" {
  description = "Grafana admin password"
  type = string
  sensitive = true
  default = "admin123!"  # À changer en production
}