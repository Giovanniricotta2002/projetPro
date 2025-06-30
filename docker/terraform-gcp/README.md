# Documentation de déploiement GCP

## 🚀 Déploiement ProjetPro sur Google Cloud Platform

### Prérequis

1. **Compte GCP** avec facturation activée
2. **Projet GCP** créé
3. **gcloud CLI** installé et configuré
4. **Terraform** installé (>= 1.5)

### 📋 Étapes de déploiement

#### 1. Configuration initiale

```bash
# Se connecter à GCP
gcloud auth login
gcloud auth application-default login

# Définir le projet par défaut
gcloud config set project YOUR_PROJECT_ID

# Activer les APIs nécessaires (optionnel, Terraform le fait automatiquement)
gcloud services enable \
  cloudresourcemanager.googleapis.com \
  compute.googleapis.com \
  run.googleapis.com \
  sqladmin.googleapis.com \
  storage.googleapis.com \
  secretmanager.googleapis.com
```

#### 2. Configuration Terraform

```bash
# Copier le fichier d'exemple
cp terraform.tfvars.example terraform.tfvars

# Éditer terraform.tfvars avec vos valeurs
nano terraform.tfvars
```

**Contenu minimum de `terraform.tfvars`:**

```hcl
project_id = "your-gcp-project-id"
jwt_secret = "your-super-secret-jwt-key"
postgres_password = "your-secure-db-password"
grafana_admin_password = "your-grafana-password"
```

#### 3. Déploiement

```bash
# Initialiser Terraform
terraform init

# Planifier le déploiement
terraform plan

# Appliquer les changements
terraform apply
```

### 🏗️ Architecture déployée

- **Cloud Run** : Backend, Frontend, Grafana (scale-to-zero)
- **Cloud SQL** : PostgreSQL (db-f1-micro)
- **Cloud Storage** : 3 buckets pour images, uploads, dashboards
- **Secret Manager** : Gestion sécurisée des mots de passe
- **VPC** : Réseau privé pour la sécurité
- **VPC Connector** : Connexion Cloud Run ↔ Cloud SQL

### 💰 Optimisations coûts

1. **Scale-to-zero** sur Cloud Run (facturation uniquement à l'usage)
2. **db-f1-micro** pour PostgreSQL (le moins cher)
3. **Stockage HDD** au lieu de SSD
4. **Région Europe-West1** (Belgique, prix avantageux)
5. **Pas de haute disponibilité** (mode zonal)

### 🔐 Sécurité

- **Pas d'IP publique** sur la base de données
- **SSL/TLS** obligatoire
- **Secrets Manager** pour les mots de passe
- **VPC privé** pour les communications internes

### 📊 Accès aux services

Après déploiement, les URLs seront affichées :

- **Frontend** : `https://frontend-xxx.run.app`
- **Backend** : `https://backend-api-xxx.run.app`  
- **Grafana** : `https://grafana-xxx.run.app`

### 🛠️ Maintenance

```bash
# Voir les ressources créées
terraform show

# Mettre à jour
terraform plan
terraform apply

# Détruire (ATTENTION: supprime tout)
terraform destroy
```

### 💡 Conseils

1. **Monitoring** : Utilisez Cloud Monitoring (gratuit jusqu'à certaines limites)
2. **Logs** : Cloud Logging automatiquement activé
3. **Backup** : Sauvegardes automatiques configurées (7 jours)
4. **Scaling** : Ajustez les limites selon votre trafic

### 🆘 Dépannage

**Erreur d'API non activée :**

```bash
gcloud services enable [API_NAME]
```

**Erreur de quota :**

- Vérifiez les quotas dans la console GCP
- Demandez une augmentation si nécessaire

**Erreur de permission :**

```bash
gcloud auth application-default login
```

### 📈 Estimation des coûts

| Service | Coût mensuel (€) |
|---------|------------------|
| Cloud SQL (f1-micro) | 15-25 |
| Cloud Run (3 services) | 5-20 |
| Cloud Storage | 2-8 |
| Réseau | 2-5 |
| **Total estimé** | **24-58€** |

*Coûts réels dépendent de l'utilisation. Scale-to-zero permet d'économiser significativement.*
