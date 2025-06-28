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