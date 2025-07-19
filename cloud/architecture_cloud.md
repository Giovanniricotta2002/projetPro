# Infrastructure Cloud - MuscuScope (GCP Cloud Run)

## Architecture actuelle déployée

```mermaid
flowchart TD
    User[Utilisateur Web]
    Internet[Internet]
    
    subgraph GCP["Google Cloud Platform"]
        subgraph CloudRun["Cloud Run Services"]
            FE[Frontend Container<br/>Vue.js + Nginx<br/>Auto-scaling 0-3]
            BE[Backend Container<br/>Symfony API<br/>Auto-scaling 0-3]
            GR[Grafana Container<br/>Monitoring<br/>Auto-scaling 0-2]
        end
        
        subgraph Data["Données"]
            DB[Cloud SQL PostgreSQL<br/>db-f1-micro<br/>Private IP]
            CS1[Cloud Storage<br/>Machine Images]
            CS2[Cloud Storage<br/>Temp Uploads]
            CS3[Cloud Storage<br/>Grafana Dashboards]
        end
        
        subgraph Security["Sécurité"]
            SM[Secret Manager<br/>JWT, Passwords]
            VPC[VPC Network<br/>Private Access]
            VPCC[VPC Connector<br/>Cloud Run ↔ VPC]
        end
    end

    User -- HTTPS --> Internet
    Internet -- HTTPS --> FE
    Internet -- HTTPS --> BE
    Internet -- HTTPS --> GR
    
    FE -- "API Calls<br/>CORS OK" --> BE
    BE -- "Private SQL" --> DB
    GR -- "Private SQL" --> DB
    
    BE -- "Files" --> CS1
    BE -- "Uploads" --> CS2
    GR -- "Dashboards" --> CS3
    
    BE -- "Secrets" --> SM
    GR -- "Secrets" --> SM
    
    BE -- "VPC Access" --> VPCC
    GR -- "VPC Access" --> VPCC
    VPCC -- "Private Network" --> VPC
    DB -- "Private IP" --> VPC
```

## Résolution du problème CORS

```mermaid
sequenceDiagram
    participant U as Utilisateur
    participant F as Frontend<br/>(Cloud Run)
    participant B as Backend<br/>(Cloud Run)
    
    Note over F,B: ❌ Problème initial
    F->>B: GET https://api.muscuscope.local/api/csrfToken
    B-->>F: CORS Error (domaine non autorisé)
    
    Note over F,B: ✅ Solution implémentée
    Note over F: Build avec VITE_API_URL=https://backend-api-*.run.app
    Note over B: CORS_ALLOW_ORIGIN=https://*-run.app
    
    U->>F: Accès à l'application
    F->>B: GET https://backend-api-66g7tud2sq-ew.a.run.app/api/csrfToken
    B-->>F: 200 OK + token CSRF
    F-->>U: Application fonctionnelle
```

## Infrastructure économique (Coûts ~30-50€/mois)

```mermaid
flowchart LR
    subgraph Compute["Compute (Cloud Run)"]
        direction TB
        CR1["Frontend: 0-3 instances<br/>256Mi RAM, 1 vCPU<br/>Scale-to-zero"]
        CR2["Backend: 0-3 instances<br/>512Mi RAM, 1 vCPU<br/>Scale-to-zero"]
        CR3["Grafana: 0-2 instances<br/>256Mi RAM, 0.5 vCPU<br/>Scale-to-zero"]
    end
    
    subgraph Storage["Stockage"]
        direction TB
        SQL["Cloud SQL PostgreSQL<br/>db-f1-micro (0.6GB RAM)<br/>10GB HDD"]
        CS["Cloud Storage<br/>STANDARD class<br/>Auto-delete lifecycle"]
    end
    
    subgraph Network["Réseau"]
        direction TB
        VPC["VPC gratuit<br/>Private Google Access"]
        VPCC["VPC Connector<br/>2-3 instances min"]
    end
    
    Compute --> Storage
    Compute --> Network
```

## Déploiement automatisé

```mermaid
flowchart TD
    Dev[Développeur]
    Script[rebuild-frontend.sh]
    
    subgraph Build["Build Process"]
        GetURL[Récupération URL Backend<br/>gcloud describe]
        BuildFE[Build Frontend<br/>docker build --build-arg]
        PushImg[Push Docker Hub<br/>giovanni2002ynov/muscuscope]
        Deploy[Deploy Cloud Run<br/>gcloud run services update]
    end
    
    subgraph Result["Résultat"]
        URLs[URLs finales<br/>Frontend + Backend]
        Test[Tests CORS<br/>curl + browser]
    end
    
    Dev --> Script
    Script --> GetURL
    GetURL --> BuildFE
    BuildFE --> PushImg
    PushImg --> Deploy
    Deploy --> URLs
    URLs --> Test
```

```mermaid
flowchart TD
    User[Utilisateur Web]
    CDN[CDN / Load Balancer]
    FE[Serveur Frontend<br/>VM/Container/App Service]
    BE[Serveur Backend<br/>VM/Container/App Service]
    DB[Base de Données Cloud]
    Storage[Stockage d’objets ex: S3/Blob Storage]
    Auth[Service d’authentification ex: Cognito/Auth0/Azure AD]
    Monitor[Service de monitoring/log CloudWatch, App Insights, etc.]

    User -- HTTP/HTTPS --> CDN
    CDN -- Route trafic --> FE
    CDN -- Route trafic --> BE
    FE -- Appels API --> BE
    BE -- SQL/API --> DB
    FE -- Fichiers statiques --> Storage
    BE -- Fichiers statiques --> Storage
    User -- Authentification --> Auth
    BE -- Logs/metrics --> Monitor
    FE -- Logs/metrics --> Monitor
```

## Infrastructure cloud cible (AWS, Auth gérée en interne)

```mermaid
flowchart TD
    A[fa:fa-user Utilisateur]

    CF[CloudFront<br/>CDN]
    S3F([S3<br/>Frontend statique])
    EC2([EC2<br/>Backend PHP<br/>Gestion Auth interne])
    RDS([RDS<br/>Base de données])
    S3([S3<br/>Stockage fichiers])
    CloudWatch([aws-cloudwatch CloudWatch<br/>Monitoring])

    A -- "HTTP/HTTPS" --> CF
    CF -- "Fichiers statiques" --> S3F
    CF -- "Appels API & Auth" --> EC2
    S3F -- "Logs" --> CloudWatch
    EC2 -- "Requêtes SQL" --> RDS
    EC2 -- "Fichiers" --> S3
    EC2 -- "Logs" --> CloudWatch
    S3 -- "Logs" --> CloudWatch


    CF@{ icon: "aws:arch-amazon-cloudfront", pos: "b"}
    S3F@{ icon: "aws:arch-amazon-simple-storage-service", pos: "b"}
    S3@{ icon: "aws:arch-amazon-simple-storage-service", pos: "b"}
    EC2@{ icon: "aws:ec2-instance-contents", pos: "b"}
    RDS@{ icon: "aws:arch-amazon-elastic-block-store", pos: "b"}
    CloudWatch@{ icon: "aws:arch-amazon-cloudwatch", pos: "b"}
```

## Infrastructure cloud cible (Azure, avec icônes)

```mermaid
flowchart TD
    A[fa:fa-user Utilisateur]

    AF([Front Door<br/>CDN / Load Balancer])
    STAF([Storage Account<br/>Static Website<br/>Frontend])
    WA([azure-appservice App Service<br/>Backend PHP<br/>Gestion Auth interne])
    SQL([SQL Database])
    ST([Storage Account<br/>Fichiers backend])
    Monitor([Monitor<br/>Logs/Monitoring])

    %% Relations
    A -- "HTTP/HTTPS" --> AF
    AF -- "Fichiers statiques" --> STAF
    AF -- "Appels API & Auth" --> WA
    STAF -- "Logs" --> Monitor
    WA -- "Requêtes SQL" --> SQL
    WA -- "Fichiers" --> ST
    WA -- "Logs" --> Monitor
    ST -- "Logs" --> Monitor

    AF@{ icon: "azure:front-door-and-cdn-profiles", pos: "b"}
    STAF@{ icon: "azure:storage-accounts-classic", pos: "b"}
    WA@{ icon: "", pos: "b"}
    SQL@{ icon: "azure:arc-postgresql", pos: "b"}
    ST@{ icon: "azure:storage-accounts-classic", pos: "b"}
    Monitor@{ icon: "azure:monitor", pos: "b"}
```
