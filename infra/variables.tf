variable "aws_region" {
  description = "The AWS region to deploy resources"
  default     = "us-east-2"
}
variable "db_name" {
  description = "Nombre de la base de datos"
  default     = "markdown2video"
}

variable "db_username" {
  description = "Usuario administrador del RDS"
  default     = "admin"
}

variable "db_password" {
  description = "Contraseña del RDS"
  sensitive   = true
  default     = "admin1234"  # Idealmente usar `terraform.tfvars` o `secrets` para esto
}
