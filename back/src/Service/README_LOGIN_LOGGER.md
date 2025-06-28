# Système de Logging Automatique des Connexions

Ce système log automatiquement toutes les tentatives de connexion via l'API `/api/login` en utilisant l'entité `LogLogin`.

## 🔧 Composants

### 1. Service Principal : `LoginLoggerService`

**Fonctionnalités :**
- ✅ Log automatique des tentatives de connexion (réussies/échouées)
- ✅ Détection et blocage des IP suspectes
- ✅ Détection et blocage des comptes avec trop de tentatives échouées
- ✅ Statistiques de connexion
- ✅ Récupération intelligente de l'IP client (avec support des proxies)

**Méthodes principales :**
```php
// Logging basique
$loginLogger->logSuccessfulLogin('username');
$loginLogger->logFailedLogin('username');
$loginLogger->logLoginAttempt('username', true/false, $customDate);

// Protection contre les attaques
$loginLogger->isIpBlocked('192.168.1.1');
$loginLogger->isLoginBlocked('username');
$loginLogger->countRecentFailedAttempts('192.168.1.1', 60); // dernières 60 minutes

// Statistiques
$stats = $loginLogger->getLoginStatistics($fromDate, $toDate);
```

### 2. Event Listener : `LoginEventListener`

**Fonctionnalités :**
- ✅ Intercepte automatiquement les appels à `/api/login`
- ✅ Log automatique basé sur le code de statut HTTP
- ✅ Ajoute des headers de sécurité en cas d'échec
- ✅ Support des événements Symfony Security

### 3. Contrôleur amélioré : `LoginController`

**Fonctionnalités :**
- ✅ Validation et authentification des utilisateurs
- ✅ Intégration automatique du logging
- ✅ Protection contre les attaques par force brute
- ✅ Support CORS pour les appels AJAX
- ✅ Gestion des erreurs complète

### 4. Administration : `LoginStatsController`

**Routes disponibles :**
```
GET /api/admin/login-logs/statistics?from=2024-01-01&to=2024-01-31
GET /api/admin/login-logs/check-ip-status/{ip}
GET /api/admin/login-logs/check-login-status/{login}
GET /api/admin/login-logs/real-time-stats
POST /api/admin/login-logs/unblock-ip/{ip}       # À implémenter
POST /api/admin/login-logs/unblock-login/{login} # À implémenter
```

## 🚀 Installation et Configuration

### 1. Vérifiez que l'entité LogLogin existe
L'entité doit avoir ces propriétés :
- `login` (string, 30 caractères)
- `ipPublic` (string, 20 caractères)
- `success` (boolean)
- `date` (DateTime avec timezone)

### 2. Créez/Mettez à jour la base de données
```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

### 3. Configuration des services (automatique)
Symfony auto-wire les services, mais vous pouvez personnaliser dans `services.yaml` :

```yaml
services:
    App\Service\LoginLoggerService:
        arguments:
            $entityManager: '@doctrine.orm.entity_manager'
            $requestStack: '@request_stack'
```

## 📊 Utilisation

### Logging automatique
Le système fonctionne automatiquement dès qu'un utilisateur appelle `/api/login`.

**Exemple d'appel API :**
```javascript
fetch('/api/login', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': 'your-csrf-token'
    },
    body: JSON.stringify({
        login: 'username',
        password: 'password'
    })
})
```

### Protection contre les attaques

**Seuils par défaut :**
- **IP bloquée** : 5 tentatives échouées en 60 minutes
- **Login bloqué** : 3 tentatives échouées en 30 minutes

**Personnalisation :**
```php
// Dans votre contrôleur ou service
$isBlocked = $loginLogger->isIpBlocked($ip, 10, 120); // 10 tentatives, 120 minutes
$isBlocked = $loginLogger->isLoginBlocked($login, 5, 60); // 5 tentatives, 60 minutes
```

### Consultation des logs

**Via l'API Admin :**
```bash
# Statistiques de la semaine dernière
curl -H "Authorization: Bearer admin-token" \
     "/api/admin/login-logs/statistics"

# Vérifier le statut d'une IP
curl -H "Authorization: Bearer admin-token" \
     "/api/admin/login-logs/check-ip-status/192.168.1.100"

# Statistiques en temps réel
curl -H "Authorization: Bearer admin-token" \
     "/api/admin/login-logs/real-time-stats"
```

**Via la base de données :**
```sql
-- Connexions réussies des dernières 24h
SELECT login, COUNT(*) as attempts, MAX(date) as last_attempt
FROM log_login 
WHERE success = 1 AND date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY login
ORDER BY attempts DESC;

-- IPs avec le plus d'échecs
SELECT ip_public, COUNT(*) as failed_attempts
FROM log_login 
WHERE success = 0 AND date >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
GROUP BY ip_public
ORDER BY failed_attempts DESC;
```

## 🔒 Sécurité

### Headers de sécurité ajoutés
En cas d'échec de connexion :
- `X-Login-Status: failed`
- `X-Login-Blocked: ip|user` (si bloqué)

### Détection d'IP intelligente
Le service détecte automatiquement l'IP réelle même derrière :
- Load balancers
- Proxies reverse
- CDN (Cloudflare, etc.)

### Codes de statut HTTP
- `200` : Connexion réussie
- `401` : Identifiants invalides
- `429` : Trop de tentatives (IP ou login bloqué)
- `400` : Paramètres manquants
- `500` : Erreur serveur

## 📈 Monitoring et Alertes

### Requêtes utiles pour le monitoring

**Pic de tentatives échouées :**
```sql
SELECT 
    DATE_FORMAT(date, '%Y-%m-%d %H:00:00') as hour,
    COUNT(*) as failed_attempts
FROM log_login 
WHERE success = 0 
    AND date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY hour
ORDER BY failed_attempts DESC;
```

**Top des IPs suspectes :**
```sql
SELECT 
    ip_public,
    COUNT(*) as total_attempts,
    SUM(success = 0) as failed_attempts,
    SUM(success = 1) as successful_attempts,
    ROUND((SUM(success = 0) / COUNT(*)) * 100, 2) as failure_rate
FROM log_login 
WHERE date >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
GROUP BY ip_public
HAVING failure_rate > 50 AND total_attempts > 5
ORDER BY failure_rate DESC, total_attempts DESC;
```

## 🛠️ Extensions possibles

### 1. Déblocage manuel
Ajouter des méthodes pour débloquer manuellement :
```php
public function clearFailedAttemptsForIp(string $ip): void
public function clearFailedAttemptsForLogin(string $login): void
```

### 2. Alertes en temps réel
Intégrer avec des services de notification :
- Email automatique pour les attaques détectées
- Webhook vers Slack/Discord
- Logs vers Elasticsearch/Kibana

### 3. Analyse avancée
- Détection de patterns géographiques
- Analyse des user agents suspects
- Corrélation temporelle des attaques

### 4. Cache Redis
Pour améliorer les performances sur les gros volumes :
```php
// Cache des compteurs de tentatives échouées
$redis->setex("failed_attempts:ip:{$ip}", 3600, $count);
```
