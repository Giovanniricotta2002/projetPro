# Guide d'utilisation des cookies httpOnly

## Principe

Les cookies httpOnly permettent de stocker les tokens JWT de manière sécurisée côté client, sans qu'ils soient accessibles via JavaScript. Cela protège contre les attaques XSS (Cross-Site Scripting).

## Service HttpOnlyCookieService

Un service dédié `HttpOnlyCookieService` centralise toute la logique de gestion des cookies httpOnly.

### Méthodes principales

```php
// Définir les cookies JWT lors de la connexion
$cookieService->setJwtCookies($response, $request, $tokens);

// Supprimer les cookies lors de la déconnexion
$cookieService->clearJwtCookies($response, $request);

// Extraire l'access token (priorité: cookie > header)
$token = $cookieService->extractAccessToken($request);

// Extraire le refresh token (priorité: cookie > body)
$refreshToken = $cookieService->extractRefreshToken($request);

// Vérifier la présence de cookies JWT
$hasCookies = $cookieService->hasJwtCookies($request);
```

## Configuration de sécurité

### Flags de sécurité automatiques

Le service applique automatiquement les flags de sécurité optimaux :

- **httpOnly**: `true` - Inaccessible via JavaScript (protection XSS)
- **secure**: `true` sur HTTPS, `false` sur HTTP
- **sameSite**: `STRICT` - Protection contre les attaques CSRF
- **path**: `/` pour access_token, `/api/tokens/refresh` pour refresh_token

### TTL des cookies

- **Access Token**: 1 heure (3600 secondes)
- **Refresh Token**: 7 jours (604800 secondes)

## Utilisation côté client

### JavaScript/Fetch API

Avec les cookies httpOnly, vous devez inclure `credentials: 'include'` dans vos requêtes :

```javascript
// Login
fetch('/api/login', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': 'your-csrf-token'
    },
    credentials: 'include', // IMPORTANT : Inclut les cookies
    body: JSON.stringify({
        login: 'username',
        password: 'password'
    })
});

// Requêtes authentifiées
fetch('/api/protected-endpoint', {
    method: 'GET',
    credentials: 'include' // Le token est automatiquement envoyé via les cookies
});

// Refresh token
fetch('/api/tokens/refresh', {
    method: 'POST',
    credentials: 'include' // Le refresh_token est lu depuis les cookies
});

// Logout
fetch('/api/tokens/logout', {
    method: 'POST',
    credentials: 'include'
});
```

### Axios

```javascript
// Configuration globale
axios.defaults.withCredentials = true;

// Ou pour des requêtes spécifiques
axios.post('/api/login', data, {
    withCredentials: true
});
```

## Avantages

1. **Protection XSS** : Les tokens ne sont pas accessibles via JavaScript
2. **Gestion automatique** : Les cookies sont envoyés automatiquement avec chaque requête
3. **Sécurité renforcée** : Combinaison de httpOnly, secure et sameSite

## Inconvénients

1. **CORS complexe** : Nécessite `allow_credentials: true`
2. **Mobile/Apps** : Plus complexe à gérer dans les applications natives
3. **Debugging** : Plus difficile à inspecter côté client

## Configuration CORS requise

```yaml
nelmio_cors:
    defaults:
        allow_credentials: true # OBLIGATOIRE pour les cookies
        allow_origin: ['https://votre-frontend.com']
        allow_methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS']
        allow_headers: ['Content-Type', 'Authorization', 'X-CSRF-Token']
```

## Variables d'environnement

```env
# .env.local
CORS_ALLOW_ORIGIN=https://votre-frontend.com
```

## Architecture hybride

L'implémentation actuelle supporte les deux approches :

1. **Cookies httpOnly** (priorité 1) : Plus sécurisé
2. **Headers Authorization** (fallback) : Compatibilité API

Cette approche hybride permet une migration progressive et une compatibilité maximale.

## Utilisation dans les contrôleurs

### Injection du service

```php
public function __construct(
    private readonly HttpOnlyCookieService $cookieService,
    // ... autres services
) {}
```

### Exemple d'utilisation

```php
// Login - Définir les cookies
$response = $this->json($loginResponse);
$this->cookieService->setJwtCookies($response, $request, $tokens);

// Endpoints protégés - Extraire le token
$token = $this->cookieService->extractAccessToken($request);

// Refresh - Extraire et redéfinir
$refreshToken = $this->cookieService->extractRefreshToken($request);
// ... refresh logic ...
$this->cookieService->setJwtCookies($response, $request, $newTokens);

// Logout - Supprimer les cookies
$response = $this->json(['message' => 'Logout successful']);
$this->cookieService->clearJwtCookies($response, $request);
```

## Tests

Le service est entièrement testé avec `HttpOnlyCookieServiceTest` qui couvre :

- Configuration des cookies avec les bons flags de sécurité
- Extraction des tokens depuis cookies et headers
- Ordre de priorité (cookies > headers/body)
- Gestion des requêtes HTTP vs HTTPS
- Suppression des cookies
