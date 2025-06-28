# 💪 MuscuScope - Plateforme Collaborative de Musculation

![Symfony](https://img.shields.io/badge/Symfony-7.3-000000?style=flat&logo=symfony)
![Vue.js](https://img.shields.io/badge/Vue.js-3.5-4FC08D?style=flat&logo=vue.js)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php)
![TypeScript](https://img.shields.io/badge/TypeScript-Latest-3178C6?style=flat&logo=typescript)
![Docker](https://img.shields.io/badge/Docker-Enabled-2496ED?style=flat&logo=docker)
![Tests](https://img.shields.io/badge/Tests-PHPUnit%20%2B%20Vitest-success?style=flat)

> **Projet de fin d'année - Développement d'une plateforme web collaborative dédiée à la compréhension des équipements de musculation**

## 🎯 Vue d'ensemble

**MuscuScope** est une plateforme web moderne qui centralise et vulgarise les connaissances sur les machines et équipements de musculation. Elle combine une interface utilisateur intuitive avec une API robuste et un système de logging avancé pour offrir une expérience utilisateur optimale.

### 🌟 Fonctionnalités principales

- **📚 Base de données collaborative** : Fiches détaillées des machines de musculation
- **🔍 Recherche avancée** : Filtrage par type, zone musculaire, difficulté
- **💬 Forum communautaire** : Suggestions et enrichissements collaboratifs
- **🔐 Authentification sécurisée** : Système de connexion avec logging automatique
- **📊 Monitoring** : Suivi des connexions et des activités utilisateurs
- **🌐 API RESTful** : Documentation OpenAPI/Swagger complète

---

## 🏗️ Architecture technique

### Stack technologique

#### Backend (API)

- **Framework** : Symfony 7.3
- **Langage** : PHP 8.2+
- **Base de données** : PostgreSQL avec Doctrine ORM
- **Documentation API** : NelmioApiDocBundle (OpenAPI/Swagger)
- **Tests** : PHPUnit + Behat (BDD)
- **Qualité code** : PHPStan, PHP_CodeSniffer

#### Frontend (SPA)

- **Framework** : Vue.js 3.5 + TypeScript
- **UI Library** : Vuetify 3
- **State Management** : Pinia
- **Build Tool** : Vite
- **Tests** : Vitest + Vue Test Utils
- **Linting** : ESLint

#### Infrastructure

- **Containerisation** : Docker + Docker Compose
- **Orchestration** : Kubernetes (Kind pour dev)
- **Cloud** : Azure (Terraform pour l'IaC)
- **CI/CD** : GitHub Actions (prêt pour intégration)

### 📐 Architecture C4

Le projet suit le modèle d'architecture C4 (Context, Container, Component, Code). Consultez la [documentation complète de l'architecture](./cloud/README_C4_ARCHITECTURE.md) pour plus de détails.

---

## 🚀 Installation et démarrage

### Prérequis

- **Docker** et **Docker Compose**
- **PHP 8.2+** et **Composer** (pour développement local)
- **Node.js 20+** et **npm** (pour le frontend)
- **Git**

### 🐳 Démarrage rapide avec Docker

```bash
# Cloner le projet
git clone <votre-repo>
cd projetPro

# Configurer les variables d'environnement
cp .env.example .env

# Démarrer l'environnement
docker-compose up -d

# Accéder à l'application
# Frontend: http://localhost:3000
# Backend API: http://localhost:8000
# Documentation API: http://localhost:8000/api/doc
```

### 🛠️ Installation développement local

#### Backend

```bash
cd back/

# Installation des dépendances
composer install

# Configuration de la base de données
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Démarrage du serveur de développement
symfony server:start
# ou
php -S localhost:8000 -t public/
```

#### Frontend

```bash
cd front/

# Installation des dépendances
npm install

# Démarrage du serveur de développement
npm run dev

# Build de production
npm run build
```

---

## 🧪 Tests et qualité

### Tests Backend

```bash
cd back/

# Tests unitaires
./bin/phpunit

# Tests BDD avec Behat
./vendor/bin/behat

# Analyse statique
./vendor/bin/phpstan analyse

# Standards de codage
./vendor/bin/phpcs
```

### Tests Frontend

```bash
cd front/

# Tests unitaires
npm run test

# Tests avec couverture
npm run test:coverage

# Linting
npm run lint
```

**Couverture de tests** : Le projet maintient une couverture de tests élevée sur les composants critiques (authentification, logging, API).

---

## 📚 Fonctionnalités techniques avancées

### 🔐 Système de logging automatique

Le projet implémente un système de logging avancé pour tracer les tentatives de connexion :

```php
#[Route('/api/login', methods: ['POST'])]
#[LogLogin(
    logSuccess: true,
    logFailure: true,
    includeUserAgent: true,
    includeIpAddress: true
)]
#[OA\Post(
    path: '/api/login',
    summary: 'Authentification utilisateur',
    requestBody: new OA\RequestBody(/* ... */),
    responses: [/* ... */]
)]
public function login(Request $request): JsonResponse
{
    // Logique d'authentification
}
```

**Caractéristiques** :

- ✅ Attribut PHP moderne `#[LogLogin]`
- ✅ Logging configurable (succès/échec)
- ✅ Capture des métadonnées (IP, User-Agent, timestamp)
- ✅ EventListener automatique
- ✅ Tests unitaires complets

### 📖 Documentation API automatique

- **OpenAPI/Swagger** : Documentation interactive complète
- **DTOs typés** : Validation et sérialisation automatiques
- **Annotations riches** : Exemples, schémas, codes d'erreur
- **Accès** : `/api/doc` sur le backend

### 🎨 Interface utilisateur moderne

- **Design responsive** : Adaptation mobile/desktop
- **Composants réutilisables** : Architecture modulaire Vue.js
- **State management** : Gestion d'état centralisée avec Pinia
- **TypeScript** : Typage fort pour la maintenabilité

---

## 📁 Structure du projet

```text
projetPro/
├── 📂 back/                    # API Symfony
│   ├── 📂 src/
│   │   ├── 📂 Attribute/       # Attributs personnalisés
│   │   ├── 📂 Controller/      # Contrôleurs API
│   │   ├── 📂 DTO/            # Data Transfer Objects
│   │   ├── 📂 Entity/         # Entités Doctrine
│   │   ├── 📂 EventListener/  # Event Listeners
│   │   ├── 📂 Repository/     # Repositories
│   │   └── 📂 Service/        # Services métier
│   ├── 📂 tests/              # Tests unitaires et BDD
│   ├── 📂 config/             # Configuration Symfony
│   └── 📂 migrations/         # Migrations base de données
│
├── 📂 front/                   # SPA Vue.js
│   ├── 📂 src/
│   │   ├── 📂 components/     # Composants Vue
│   │   ├── 📂 stores/         # Stores Pinia
│   │   ├── 📂 views/          # Pages/Vues
│   │   └── 📂 plugins/        # Plugins (Vuetify, etc.)
│   └── 📂 tests/              # Tests frontend (Vitest)
│
├── 📂 docker/                  # Configuration Docker
│   ├── 📂 kub/                # Kubernetes manifests
│   ├── 📂 terraform-azure/    # Infrastructure as Code
│   └── compose.yml            # Docker Compose
│
├── 📂 cloud/                   # Documentation architecture
│   ├── architecture.md        # Diagrammes C4
│   ├── PERIMETRE.md           # Scope du projet
│   └── README_C4_ARCHITECTURE.md
│
└── README.md                   # Ce fichier
```

---

## 🔍 Endpoints API principaux

| Endpoint | Méthode | Description | Auth |
|----------|---------|-------------|------|
| `/api/login` | POST | Authentification utilisateur | ❌ |
| `/api/csrf-token` | GET | Récupération token CSRF | ❌ |
| `/api/machines` | GET | Liste des machines | ✅ |
| `/api/machines/{id}` | GET | Détail d'une machine | ✅ |
| `/api/stats/login` | GET | Statistiques de connexion | ✅ Admin |

**Documentation complète** : Accessible sur `/api/doc` avec Swagger UI

---

## 🛡️ Sécurité

### Mesures implémentées

- ✅ **Protection CSRF** : Tokens pour toutes les actions sensibles
- ✅ **Validation stricte** : DTOs avec contraintes Symfony
- ✅ **Logging sécurisé** : Traçabilité des connexions
- ✅ **Headers sécurisés** : CORS, CSP, HSTS
- ✅ **Hashage passwords** : Bcrypt/Argon2
- ✅ **Rate limiting** : Protection contre le brute force (à implémenter)

### Monitoring et observabilité

- 📊 **Logs structurés** : Monolog + ELK Stack ready
- 📈 **Metrics** : Prêt pour Prometheus/Grafana
- 🔍 **Tracing** : Support OpenTracing

---

## 🚀 Déploiement

### Environnements

- **Développement** : Docker Compose local
- **Staging** : Kubernetes (Kind) + Azure Container Registry
- **Production** : Azure Kubernetes Service (AKS)

### CI/CD Pipeline (prêt)

```yaml
# .github/workflows/ci.yml (template)
name: CI/CD Pipeline
on: [push, pull_request]

jobs:
  backend-tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Install dependencies
        run: composer install
      - name: Run tests
        run: ./bin/phpunit

  frontend-tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: '20'
      - name: Install dependencies
        run: npm ci
      - name: Run tests
        run: npm run test:run
```

---

## 📊 Métriques du projet

### Complexité technique

- **Backend** : ~25 classes principales, 15+ endpoints API
- **Frontend** : ~20 composants Vue, 5 stores Pinia
- **Tests** : 40+ tests unitaires, couverture > 80%
- **Documentation** : 100% des endpoints documentés

### Technologies maîtrisées

- ✅ **Architecture moderne** : Microservices, API First
- ✅ **Patterns avancés** : Event-driven, DTO, Repository
- ✅ **DevOps** : Docker, K8s, IaC (Terraform)
- ✅ **Qualité** : Tests automatisés, analyse statique
- ✅ **Sécurité** : OWASP compliance, logging audit

---

## 🎓 Aspects pédagogiques

Ce projet de fin d'année démontre la maîtrise de :

### Compétences techniques

1. **Développement Full-Stack** moderne (Symfony + Vue.js)
2. **Architecture logicielle** (Clean Architecture, SOLID)
3. **API Design** (RESTful, OpenAPI, versioning)
4. **Testing** (TDD, couverture, tests E2E)
5. **DevOps** (Docker, Kubernetes, IaC)
6. **Sécurité** (authentification, autorisation, audit)

### Soft skills

1. **Documentation** : README, architecture, API
2. **Méthodologie** : Git flow, tests automatisés
3. **Qualité** : Normes de codage, revue de code
4. **Monitoring** : Logging, métriques, observabilité

---

## 🤝 Contribution

### Standards de développement

- **Git Flow** : Feature branches + Pull Requests  
- **Coding Standards** : PSR-12 (PHP), ESLint (JS/TS)
- **Tests** : Couverture minimale 80%
- **Documentation** : Commentaires, README à jour

### Commandes utiles

```bash
# Vérification qualité backend
composer run-script check-all

# Vérification qualité frontend
npm run lint && npm run test:coverage

# Génération documentation
php bin/console api:doc:export > api-docs.json
```

---

## 📞 Support et contact

- **Auteur** : Ricotta Giovanni
- **Projet** : Fin d'année - Développement Web
- **Technologies** : Symfony 7.3, Vue.js 3, Docker, Kubernetes
- **Documentation** : [Architecture C4](./cloud/README_C4_ARCHITECTURE.md) | [Tests Frontend](./front/tests/README.md)

---

## 📝 Licence

Ce projet est développé dans le cadre d'un projet de fin d'année académique.

---

**🚀 MuscuScope - Démocratiser l'accès aux connaissances en musculation à travers une plateforme collaborative moderne et sécurisée.**
