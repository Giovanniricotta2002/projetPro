terraform {
  required_providers {
    azurerm = {
      source  = "hashicorp/azurerm"
      version = ">= 3.65.0"  # Exemple, vérifie la dernière version stable
    }
  }
}

provider "azurerm" {
  features {
    resource_group {
      prevent_deletion_if_contains_resources = true
    }
  }

  subscription_id = "d9321465-d604-4622-bda3-42776fbb288b"
}