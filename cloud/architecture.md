# ?

```mermaid
flowchart TD
    subgraph Utilisateur
        U["Utilisateur<br>navigateur web"]
    end

    subgraph Frontend["Frontend (front) - Vue.js/TypeScript"]
        FE_UI["Interface Utilisateur<br>Pages, Composants"]
        FE_Routes["Gestionnaire de routes"]
        FE_API["Service d'accès API"]
        FE_Store["Gestion d'état (Store)"]
    end

    subgraph Backend["Backend (back) - PHP/Twig"]
        BE_API["Contrôleurs API<br>REST ou autre"]
        BE_Auth["Module Authentification"]
        BE_Service["Services Métier"]
        BE_DB["Accès Base de Données"]
        BE_Templates["Templates Twig<br>si SSR"]
    end

    DB(("Base de données<br>MySQL/PostgreSQL/etc."))

    %% Relations Utilisateur
    U -- HTTP(S) --> FE_UI

    %% Relations Front <-> Back
    FE_API -- Requêtes HTTP/JSON --> BE_API
    FE_UI -- utilise --> FE_Routes
    FE_UI -- utilise --> FE_Store
    FE_Store -- utilise --> FE_API

    %% Relations Backend interne
    BE_API -- utilise --> BE_Auth
    BE_API -- utilise --> BE_Service
    BE_Service -- utilise --> BE_DB
    BE_API -- utilise --> BE_Templates

    %% Base de données
    BE_DB -- SQL --> DB
```

## Architecture cible

```mermaid
flowchart TD
    U["Utilisateur<br>navigateur web"]
    
    subgraph Frontend ["Frontend (front) - Vue.js/TypeScript"]
        FE_UI["Interface Utilisateur<br>Pages, Composants"]
        FE_Routes["Gestionnaire de routes"]
        FE_API["Service d'accès API"]
        FE_Store["Gestion d'état (Store)"]
    end

    subgraph Backend ["Backend (back) - PHP/Twig"]
        BE_API["Contrôleurs API REST"]
        BE_Auth["Module Authentification"]
        BE_Service["Services Métier"]
        BE_DB["Accès Base de Données"]
        BE_Templates["Templates Twig<br>SSR possible"]
    end

    DB(("Base de données<br>MySQL/PostgreSQL..."))

    %% Utilisateur vers Frontend
    U -- HTTP(S) --> FE_UI

    %% Frontend interne
    FE_UI -- utilise --> FE_Routes
    FE_UI -- utilise --> FE_Store
    FE_Store -- utilise --> FE_API

    %% Frontend vers Backend
    FE_API -- Requêtes HTTP/JSON --> BE_API

    %% Backend interne
    BE_API -- utilise --> BE_Auth
    BE_API -- utilise --> BE_Service
    BE_Service -- utilise --> BE_DB
    BE_API -- utilise --> BE_Templates

    %% Backend vers DB
    BE_DB -- SQL --> DB

```

## Modèle C4 - Vue d'ensemble

### Niveau 1 - Context (Contexte Système)

```mermaid
C4Context
    title Contexte Système - Application Web Sécurisée

    Person(user, "Utilisateur", "Utilisateur de l'application web")
    
    System(webapp, "Application Web", "Application full-stack avec authentification sécurisée et logging automatique")
    
    SystemDb(database, "Base de Données", "Stockage des données utilisateurs, logs de connexion, et contenus")
    
    System_Ext(browser, "Navigateur Web", "Interface utilisateur")
    System_Ext(api_docs, "Documentation API", "OpenAPI/Swagger générée automatiquement")

    Rel(user, webapp, "Utilise", "HTTPS")
    Rel(webapp, database, "Lit/Écrit", "SQL/Doctrine ORM")
    Rel(user, browser, "Interagit via")
    Rel(webapp, api_docs, "Génère", "NelmioApiDocBundle")
```

### Niveau 2 - Containers (Conteneurs)

```mermaid
C4Container
    title Conteneurs - Architecture Applicative

    Person(user, "Utilisateur")

    Container_Boundary(webapp, "Application Web") {
        Container(frontend, "Frontend SPA", "Vue.js, TypeScript, Vite", "Interface utilisateur reactive")
        Container(backend, "Backend API", "PHP 8.3, Symfony 7.3", "API REST avec auth et logging")
        Container(webserver, "Serveur Web", "Nginx/Apache", "Reverse proxy et assets statiques")
    }

    ContainerDb(database, "Base de Données", "MySQL/PostgreSQL", "Persistance des données")
    
    Container_Ext(docs, "Documentation", "OpenAPI/Swagger UI", "Documentation API auto-générée")

    Rel(user, frontend, "Utilise", "HTTPS")
    Rel(frontend, backend, "Appelle", "JSON/HTTPS, CSRF protected")
    Rel(backend, database, "Persiste", "Doctrine ORM")
    RelIndex(backend, docs, "Génère", "4")
    Rel(webserver, frontend, "Sert", "Assets statiques")
    Rel(webserver, backend, "Proxy vers", "FastCGI/FPM")
```

### Niveau 3 - Components (Composants Backend)

```mermaid
C4Component
    title Composants Backend - Module Authentification et Logging

    Container_Boundary(backend, "Backend Symfony") {
        Component(loginController, "LoginController", "Controller", "Gère l'authentification utilisateur")
        Component(csrfController, "ApiCSRFTokenController", "Controller", "Génère et valide les tokens CSRF")
        
        Component(logLoginAttribute, "LogLogin Attribute", "PHP Attribute", "Attribut pour marquer les actions à logger")
        Component(logLoginListener, "LogLoginAttributeListener", "Event Listener", "Intercepte et log automatiquement les tentatives de connexion")
        Component(loginLoggerService, "LoginLoggerService", "Service", "Service métier pour le logging des connexions")
        
        Component(dtos, "Response DTOs", "Data Transfer Objects", "DTOs typés pour les réponses API")
        Component(entities, "Entities", "Doctrine Entities", "Entités métier (Utilisateur, LogLogin, etc.)")
        Component(repositories, "Repositories", "Doctrine Repositories", "Accès aux données")
        
        Component(security, "Security Component", "Symfony Security", "Gestion de l'authentification")
        Component(serializer, "InitSerializerService", "Service", "Configuration de la sérialisation")
    }

    ContainerDb(database, "Base de Données")
    Container_Ext(docs, "Documentation OpenAPI")

    Rel(loginController, logLoginAttribute, "Utilise", "#[LogLogin]")
    Rel(logLoginListener, loginLoggerService, "Utilise", "DI")
    Rel(loginLoggerService, entities, "Crée", "LogLogin entity")
    Rel(loginController, dtos, "Retourne", "LoginSuccessResponseDTO")
    Rel(csrfController, dtos, "Retourne", "CsrfTokenResponseDTO")
    Rel(loginController, security, "Utilise", "Authentication")
    Rel(entities, repositories, "Utilisées par")
    Rel(repositories, database, "Persiste vers")
    Rel(loginController, docs, "Documente", "OpenAPI annotations")
    Rel(logLoginListener, logLoginAttribute, "Écoute", "Kernel events")
    Rel(serializer, dtos, "Sérialise")
```

### Niveau 4 - Code (Détail d'implémentation)

```mermaid
C4Component
    title Détail Code - Système de Logging Automatique

    Container_Boundary(logging_system, "Système de Logging Automatique") {
        Component(attribute_def, "LogLogin::class", "PHP 8+ Attribute", "
            #[Attribute(AttributeTargets::METHOD)]
            - string $action
            - bool $logSuccess  
            - bool $logFailure
            - int $maxAttempts
            - int $blockDuration")
        
        Component(listener_impl, "LogLoginAttributeListener", "Event Subscriber", "
            - onKernelController()
            - onKernelResponse()  
            - onKernelException()
            Gère le cycle de logging automatique")
            
        Component(service_impl, "LoginLoggerService", "Business Service", "
            - logLoginAttempt()
            - isIpBlocked()
            - isUserBlocked()
            - getLoginStats()
            Logique métier centralisée")
            
        Component(dto_impl, "DTOs Structure", "Response Objects", "
            - LoginSuccessResponseDTO
            - ErrorResponseDTO  
            - CsrfTokenResponseDTO
            - PendingLoginLogDTO
            Réponses API typées")
    }

    Container_Boundary(persistence, "Couche Persistance") {
        Component(entity_loglogin, "LogLogin Entity", "Doctrine Entity", "
            - id, ipAddress, userIdentifier
            - success, attemptTime, userAgent
            - blockedUntil, action")
        Component(repository_loglogin, "LogLoginRepository", "Doctrine Repository", "
            - findRecentAttempts()
            - countFailedAttempts()
            - findBlockedEntries()")
    }

    ContainerDb(db_table, "log_login table")

    Rel(listener_impl, attribute_def, "Lit les métadonnées")
    Rel(listener_impl, service_impl, "Délègue à")
    Rel(service_impl, entity_loglogin, "Crée/Met à jour")
    Rel(entity_loglogin, repository_loglogin, "Gérée par")
    Rel(repository_loglogin, db_table, "Persiste vers")
    Rel(service_impl, dto_impl, "Utilise pour les stats")
```

## Tests et Qualité

```mermaid
flowchart TB
    subgraph Tests["Couverture de Tests"]
        UT["Tests Unitaires<br/>- DTOs: 4 classes testées<br/>- Services: LoginLoggerService, InitSerializerService<br/>- Attributes: LogLogin<br/>- EventListeners: LogLoginAttributeListener"]
        IT["Tests d'Intégration<br/>- Controllers: LoginController, ApiCSRFTokenController<br/>- Repositories: LogLoginRepository"]
        E2E["Tests E2E<br/>- Flux d'authentification complet<br/>- Validation CSRF<br/>- Logging automatique"]
    end
    
    subgraph Quality["Outils Qualité"]
        PHPSTAN["PHPStan<br/>Analyse statique"]
        PHPCS["PHP CodeSniffer<br/>Standards de code"]
        PHPUNIT["PHPUnit<br/>Tests automatisés"]
    end
    
    subgraph Docs["Documentation"]
        OPENAPI["OpenAPI/Swagger<br/>Documentation API auto-générée"]
        PHPDOC["PHPDoc<br/>Documentation code"]
        README["README techniques<br/>Guide d'utilisation"]
    end

    UT --> Quality
    IT --> Quality  
    E2E --> Quality
    Quality --> Docs
```
