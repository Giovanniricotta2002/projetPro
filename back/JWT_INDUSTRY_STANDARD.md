# Standard de Sécurité JWT - Industrie 2025

## 🏆 Configuration Recommandée (Standard OWASP)

### Durées de vie des tokens
- **Access Token**: 15-30 minutes (production), 1 heure (développement)
- **Refresh Token**: 7-30 jours (selon le niveau de sécurité)

### Stockage
- **Access Token**: Cookie httpOnly ou Authorization header
- **Refresh Token**: Cookie httpOnly uniquement (plus sécurisé)

### Refresh Strategy
- **Manuel obligatoire** avec intercepteurs côté client
- **Rate limiting**: 10 refresh/heure, 50/jour par utilisateur
- **Audit logging** de tous les refresh

## 📝 Implémentation Standard

### 1. Côté Client (JavaScript/TypeScript)
```javascript
// Intercepteur Axios - Standard industrie
axios.interceptors.response.use(
    response => response,
    async error => {
        const originalRequest = error.config;
        
        if (error.response?.status === 401 && !originalRequest._retry) {
            originalRequest._retry = true;
            
            try {
                // Refresh manuel explicite
                await fetch('/api/tokens/refresh', {
                    method: 'POST',
                    credentials: 'include'
                });
                
                // Retry avec nouveau token
                return axios(originalRequest);
            } catch (refreshError) {
                // Redirect vers login
                window.location.href = '/login';
                return Promise.reject(refreshError);
            }
        }
        
        return Promise.reject(error);
    }
);
```

### 2. Côté Serveur (Symfony/PHP)
```php
// Rate limiting sur refresh
if (!$this->isRefreshAllowed($request, $user)) {
    return $this->json(['error' => 'Rate limit exceeded'], 429);
}

// Log de sécurité obligatoire
$this->logger->info('Token refresh requested', [
    'user_id' => $user->getId(),
    'ip' => $request->getClientIp(),
    'user_agent' => $request->headers->get('User-Agent')
]);
```

## 🛡️ Contrôles de Sécurité Obligatoires

1. **Rate Limiting**
   - 10 refresh/heure par IP
   - 50 refresh/jour par utilisateur
   - Blocage temporaire en cas d'abus

2. **Audit Trail**
   - Log de chaque tentative de refresh
   - Monitoring des patterns suspects
   - Alertes automatiques

3. **Validation Géographique**
   - Détection de changement d'IP drastique
   - Vérification User-Agent
   - Blocage en cas d'anomalie

4. **Révocation de Token**
   - Possibilité d'invalider tous les tokens d'un utilisateur
   - Blacklist temporaire en cas de compromission
   - Notification à l'utilisateur

## 🚨 Red Flags à Éviter

❌ Refresh automatique silencieux
❌ Pas de rate limiting
❌ Tokens longue durée (>1h pour access)
❌ Stockage en localStorage
❌ Pas de logging des refresh
❌ Pas de détection d'anomalies

## ✅ Checklist de Conformité

- [ ] Access token ≤ 30 minutes
- [ ] Refresh token ≤ 30 jours
- [ ] Cookies httpOnly uniquement
- [ ] Rate limiting implémenté
- [ ] Audit logging complet
- [ ] Détection d'anomalies
- [ ] Révocation de tokens
- [ ] Tests de sécurité automatisés

## 📚 Références Standards

- OWASP JWT Security Cheat Sheet
- RFC 6749 (OAuth 2.0)
- RFC 7519 (JWT)
- NIST SP 800-63B (Authentication Guidelines)
- CWE-384 (Session Fixation)
