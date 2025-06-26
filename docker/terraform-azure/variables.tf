variable "pg_admin_user" {
  description = "Nom d'utilisateur admin pour PostgreSQL"
  type        = string
  default     = "adminuser"
}

variable "pg_admin_password" {
  description = "Mot de passe admin pour PostgreSQL"
  type        = string
  sensitive   = true
}