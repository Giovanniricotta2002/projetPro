# 📋 Cahier de Recettes - MuscuScope

> **Critère C2.3.1** - Élaborer le cahier de recettes en rédigeant les scénarios de tests et les résultats attendus afin de détecter les anomalies de fonctionnement et les régressions éventuelles.

## 🎯 Objectifs du cahier de recettes

Ce document définit l'ensemble des tests de validation fonctionnelle pour s'assurer que l'application MuscuScope répond aux exigences spécifiées et fonctionne correctement en conditions réelles d'utilisation.

### 📋 Fonctionnalités attendues couvertes

- ✅ **Authentification** : Connexion/déconnexion sécurisée
- ✅ **Gestion utilisateurs** : CRUD complet avec rôles
- ✅ **Interface responsive** : Mobile, tablet, desktop
- ✅ **Sécurité OWASP** : Protection contre les 10 failles principales
- ✅ **Accessibilité WCAG** : Conformité niveau AA
- ✅ **Performance** : Temps de réponse < 2s
- ✅ **Intégration API** : Endpoints REST sécurisés

---

## 🏗️ Environnements de test

### Environnement de Recette

- **URL Frontend** : `https://staging-frontend.muscuscope.com`
- **URL Backend API** : `https://staging-api.muscuscope.com`
- **Base de données** : PostgreSQL staging avec jeu de données de test
- **Utilisateurs de test** : Comptes pré-configurés avec différents rôles

### Jeu de données de test

```sql
-- Utilisateur Admin
Email: admin@test.com / Mot de passe: TestAdmin123!

-- Utilisateur Standard
Email: user@test.com / Mot de passe: TestUser123!

-- Utilisateur Editeur
Email: editeur@test.com / Mot de passe: TestEditeur123!
```

---

## 📝 Scénarios de Tests Fonctionnels

### 🔐 **Bloc 1 : Authentification et Sécurité**

#### **TEST-AUTH-001 : Connexion utilisateur valide**

**Prérequis** : Utilisateur avec compte valide  
**Étapes** :

1. Accéder à la page de connexion
2. Saisir email : `user@test.com`
3. Saisir mot de passe : `TestUser123!`
4. Cliquer sur "Se connecter"

**Résultat attendu** :

- ✅ Redirection vers le dashboard utilisateur
- ✅ Token JWT généré et stocké
- ✅ Log de connexion enregistré avec IP et User-Agent
- ✅ Menu de navigation adapté au rôle affiché

**Critères de validation** :

- Temps de réponse < 2 secondes
- Token valide pendant 24h
- Audit trail complet

---

#### **TEST-AUTH-002 : Connexion avec identifiants invalides**

**Prérequis** : Tentative de connexion avec mauvais identifiants  
**Étapes** :

1. Accéder à la page de connexion
2. Saisir email : `user@test.com`
3. Saisir mot de passe : `MauvaisMotDePasse`
4. Cliquer sur "Se connecter"

**Résultat attendu** :

- ❌ Échec de l'authentification
- ✅ Message d'erreur : "Identifiants incorrects"
- ✅ Log d'échec enregistré avec détails
- ✅ Aucune information sensible divulguée
- ✅ Rate limiting après 5 tentatives

---

#### **TEST-AUTH-003 : Protection CSRF**

**Prérequis** : Token CSRF actif  

**Étapes** :

1. Récupérer un token CSRF via `/api/csrf-token`
2. Effectuer une action sensible avec token valide
3. Effectuer la même action avec token expiré/invalide

**Résultat attendu** :

- ✅ Action autorisée avec token valide
- ❌ Action bloquée avec token invalide
- ✅ Erreur 403 Forbidden retournée
- ✅ Log de sécurité généré

---

### 📚 **Bloc 2 : Gestion des Machines de Musculation**

#### **TEST-MACHINE-001 : Consultation liste des machines**

**Prérequis** : Utilisateur connecté  

**Étapes** :

1. Accéder à `/machines`
2. Vérifier l'affichage de la liste
3. Tester la pagination
4. Tester les filtres par catégorie

**Résultat attendu** :

- ✅ Liste des machines
- ✅ Images des machines chargées correctement
- ✅ Filtres fonctionnels (muscle ciblé, difficulté)
- ✅ Temps de chargement < 3 secondes
- ✅ Interface responsive mobile/desktop

---

#### **TEST-MACHINE-002 : Consultation détail d'une machine**

**Prérequis** : Machine existante en base  

**Étapes** :

1. Cliquer sur une machine depuis la liste
2. Vérifier les informations détaillées
3. Tester les interactions (editer, supprimé)

**Résultat attendu** :

- ✅ Affichage complet des informations machine
- ✅ Images haute résolution disponibles
- ✅ Conseils d'utilisation affichés
- ✅ Boutons d'action fonctionnels
- ✅ URL SEO-friendly

---

#### **TEST-MACHINE-003 : Recherche avancée**

**Prérequis** : Base de données avec machines diverses  

**Étapes** :

1. Utiliser la barre de recherche
2. Tester recherche par nom : "développé"
3. Tester recherche par muscle : "pectoraux"
4. Tester recherche combinée

**Résultat attendu** :

- ✅ Résultats pertinents retournés
- ✅ Autocomplétion fonctionnelle
- ✅ Tri par pertinence
- ✅ Gestion "aucun résultat"
- ✅ Performance < 1 seconde

---

### 💬 **Bloc 3 : Forum Communautaire**

#### **TEST-FORUM-001 : Création d'une suggestion**

**Prérequis** : Utilisateur connecté avec permissions  

**Étapes** :

1. Accéder au forum
2. Cliquer sur "Nouvelle suggestion"
3. Remplir le formulaire (titre, description, machine)
4. Soumettre la suggestion

**Résultat attendu** :

- ✅ Formulaire de création accessible
- ✅ Validation côté client et serveur
- ✅ Suggestion créée avec statut "En attente"
- ✅ Notification email aux modérateurs
- ✅ Redirection vers la suggestion créée

---

#### **TEST-FORUM-002 : Modération des suggestions**

**Prérequis** : Utilisateur modérateur connecté  

**Étapes** :

1. Accéder au panel de modération
2. Consulter les suggestions en attente
3. Approuver une suggestion
4. Rejeter une suggestion avec motif

**Résultat attendu** :

- ✅ Interface de modération accessible
- ✅ Actions d'approbation/rejet fonctionnelles
- ✅ Historique des actions enregistré
- ✅ Notifications aux auteurs
- ✅ Mise à jour des statuts en temps réel

---

### 📊 **Bloc 4 : API et Performance**

#### **TEST-API-001 : Documentation API accessible**

**Prérequis** : API déployée  

**Étapes** :

1. Accéder à `/api/doc`
2. Tester un endpoint depuis Swagger UI
3. Vérifier les exemples de réponses

**Résultat attendu** :

- ✅ Documentation Swagger complète
- ✅ Tous les endpoints documentés
- ✅ Exemples fonctionnels
- ✅ Schémas de données corrects
- ✅ Interface Try-it fonctionnelle

---

#### **TEST-API-002 : Performance et monitoring**

**Prérequis** : Environnement avec monitoring  

**Étapes** :

1. Effectuer 100 requêtes simultanées sur `/api/machines`
2. Vérifier les métriques de performance
3. Tester la gestion de la charge

**Résultat attendu** :

- ✅ Temps de réponse moyen < 200ms
- ✅ Taux de succès > 99%
- ✅ Pas de memory leak détecté
- ✅ Logs d'erreur centralisés
- ✅ Alertes fonctionnelles

---

## 🧪 Tests de Régression

### **REG-001 : Migration de données**

**Contexte** : Mise à jour de version avec migration DB  

**Vérifications** :

- ✅ Intégrité des données existantes
- ✅ Nouveaux champs correctement initialisés
- ✅ Performances non dégradées
- ✅ Compatibilité ascendante API

### **REG-002 : Compatibilité navigateurs**

**Contexte** : Nouvelle version frontend  

**Vérifications** :

- ✅ Chrome/Edge (versions N et N-1)
- ✅ Firefox (versions N et N-1)  
- ✅ Safari (version actuelle)
- ✅ Mobile (iOS/Android)

---

## 📱 Tests Multi-dispositifs

### **MOBILE-001 : Interface responsive**

**Dispositifs de test** :

- 📱 iPhone 12/13/14 (iOS)
- 📱 Samsung Galaxy S21/S22 (Android)
- 📱 iPad (iPadOS)

**Vérifications** :

- ✅ Navigation tactile fluide
- ✅ Formulaires utilisables
- ✅ Images adaptatives
- ✅ Performance acceptable
- ✅ Fonctionnalités core disponibles

---

## 🔒 Tests de Sécurité

### **SEC-001 : Vulnérabilités OWASP Top 10**

**Tests à effectuer** :

1. **Injection** : Tests SQLi sur formulaires
2. **Authentification** : Brute force, session management
3. **Exposition de données** : Logs, erreurs, APIs
4. **XXE** : Upload de fichiers XML
5. **Contrôle d'accès** : Élévation de privilèges
6. **Mauvaise configuration** : Headers sécurisé
7. **XSS** : Injection de scripts
8. **Désérialisation** : Objets malveillants
9. **Composants vulnérables** : Scan dépendances
10. **Monitoring** : Détection intrusions

**Résultat attendu** : ✅ Aucune vulnérabilité critique

---

## 📈 Tests de Performance

### **PERF-001 : Charge nominale**

**Profil de charge** :

- 👥 100 utilisateurs simultanés
- ⏱️ Durée : 30 minutes
- 🎯 Scénarios mixtes (70% lecture, 30% écriture)

**Critères de succès** :

- ✅ Temps de réponse 95th percentile < 1s
- ✅ Taux d'erreur < 0.1%
- ✅ CPU < 70%
- ✅ Mémoire stable

### **PERF-002 : Charge de pointe**

**Profil de charge** :

- 👥 500 utilisateurs simultanés
- ⏱️ Durée : 10 minutes
- 🎯 Pic de trafic simulé

**Critères de succès** :

- ✅ Application reste disponible
- ✅ Dégradation gracieuse si besoin
- ✅ Récupération automatique
- ✅ Alertes déclenchées

---

## 📊 Métriques et Rapports

### Dashboard de suivi des tests

```markdown
| Bloc de tests | Total | Passés | Échecs | Taux de succès |
|---------------|-------|---------|---------|----------------|
| Authentification | 3 | 3 | 0 | 100% |
| Machines | 3 | 3 | 0 | 100% |
| Forum | 2 | 2 | 0 | 100% |
| API | 2 | 2 | 0 | 100% |
| Sécurité | 1 | 1 | 0 | 100% |
| Performance | 2 | 2 | 0 | 100% |
| **TOTAL** | **13** | **13** | **0** | **100%** |
```

### Rapport de recette type

```markdown
## 📋 Rapport de Recette - Version 1.2.0

**Date** : 2025-07-20  
**Environnement** : Staging  
**Testeur** : Équipe QA  

### ✅ Tests validés
- [x] Tous les scénarios critiques passés
- [x] Performance dans les SLA
- [x] Sécurité conforme
- [x] Mobile fonctionnel

### ⚠️ Points d'attention
- Temps de chargement mobile légèrement élevé (optimisation en cours)
- Message d'erreur API à clarifier (ticket #456)

### 🚀 Validation
- ✅ **Validation QA** : OK pour mise en production
- ✅ **Validation PO** : Fonctionnalités conformes
- ✅ **Validation Tech** : Architecture stable
```

---

## 🔄 Processus de Recette

---

## 🛡️ Tests Structurels et de Sécurité

### **Tests de Sécurité OWASP Top 10**

#### **TEST-SEC-001 : Injection SQL (A01)**

**Objectif** : Vérifier la protection contre les injections SQL  

**Étapes** :

1. Tenter injection dans champ de recherche : `'; DROP TABLE users; --`
2. Vérifier paramètres POST avec payload malveillant
3. Tester tous les endpoints API avec caractères spéciaux

**Résultat attendu** :

- ✅ Requêtes préparées bloquent les injections
- ✅ Validation des entrées côté serveur
- ✅ Messages d'erreur génériques (pas de révélation de structure DB)

#### **TEST-SEC-002 : Broken Authentication (A02)**

**Objectif** : Vérifier la robustesse de l'authentification  

**Étapes** :

1. Tester force brute sur `/login` (> 5 tentatives)
2. Vérifier expiration des tokens JWT (> 24h)
3. Tester réutilisation de tokens après logout

**Résultat attendu** :

- ✅ Limitation du taux de tentatives (rate limiting)
- ✅ Tokens invalidés après logout
- ✅ Rotation automatique des secrets JWT

#### **TEST-SEC-003 : Sensitive Data Exposure (A03)**

**Objectif** : Vérifier la protection des données sensibles  

**Étapes** :

1. Inspecter le localStorage/sessionStorage
2. Analyser les réponses API (pas de mots de passe)
3. Vérifier HTTPS obligatoire

**Résultat attendu** :

- ✅ Aucun mot de passe en clair stocké
- ✅ Chiffrement AES-256 pour données sensibles
- ✅ Headers de sécurité présents (HSTS, CSP)

### **Tests Structurels**

#### **TEST-STRUCT-001 : Architecture en couches**

**Objectif** : Vérifier la séparation des responsabilités  

**Critères** :

- ✅ Couche présentation (Vue.js) isolée
- ✅ Couche métier (Services Symfony) indépendante
- ✅ Couche données (Repository pattern) découplée

#### **TEST-STRUCT-002 : Qualité du code**

**Objectif** : Validation des standards de développement  

**Outils** : PHPStan niveau 8, ESLint strict

**Critères** :

- ✅ Couverture tests > 80%
- ✅ Complexité cyclomatique < 10
- ✅ Respect PSR-12 et conventions TypeScript

---

## 📊 Plan d'Exécution des Tests

### 1. Préparation

- [ ] Déploiement version staging
- [ ] Initialisation jeu de données test
- [ ] Vérification environnement

### 2. Exécution

- [ ] Tests fonctionnels selon scenarios
- [ ] Tests de régression automatisés
- [ ] Tests de performance
- [ ] Tests de sécurité

### 3. Validation

- [ ] Analyse des résultats
- [ ] Documentation des anomalies
- [ ] Décision GO/NO-GO production

### 4. Suivi

- [ ] Correction des anomalies
- [ ] Re-test des corrections
- [ ] Validation finale

---

## 📞 Contacts et Responsabilités

| Rôle | Responsable | Contact |
|------|-------------|---------|
| **Product Owner** | Giovanni Ricotta | <giovanniricotta2002@gmail.com> |
| **Tech Lead** | Équipe Dev | <giovanniricotta2002@gmail.com> |
| **QA Lead** | Équipe Test | <giovanniricotta2002@gmail.com> |
| **DevOps** | Équipe Infra | <giovanniricotta2002@gmail.com> |

---

## 📚 Annexes

### Outils de test utilisés

- **Tests fonctionnels** : Playwright, Cypress
- **Tests API** : Postman, Bruno
- **Tests performance** : K6, Apache Bench
- **Tests sécurité** : OWASP ZAP, Burp Suite
- **Monitoring** : Grafana

### Templates de rapports

- [Template Rapport de Bug](./templates/RAPPORT_BUG.md)
- [Template Test Case](./templates/TEST_CASE.md)
- [Template Rapport Performance](./templates/RAPPORT_PERFORMANCE.md)

---

**🎯 Ce cahier de recettes garantit la qualité et la fiabilité de MuscuScope avant sa mise en production.**
