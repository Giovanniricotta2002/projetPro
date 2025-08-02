# Changelog - MuscuScope

Toutes les modifications notables de ce projet seront documentées dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/),
et ce projet respecte le [Versioning Sémantique](https://semver.org/lang/fr/).

## [Non publié]

### 🔧 En cours de développement

- Amélioration des performances de cache
- Optimisation des requêtes base de données

---

## [1.2.0] - 2025-08-02

### ✨ Nouvelles fonctionnalités

- **Scripts d'automatisation** : Ajout de scripts complets pour déploiement, maintenance et monitoring
- **Gestion des forums** : Implémentation des catégories de forum et gestion complète
- **Profil utilisateur** : Nouvelle vue de profil avec gestion des informations personnelles
- **Création de machines** : Interface dédiée pour l'ajout de nouveau matériel

### 🐛 Corrections de bugs

- **DTOs** : Correction des virgules manquantes dans les constructeurs Machine, Message, Post et Utilisateur
- **API** : Spécification du type générique pour les requêtes API dans postMessage
- **Schéma** : Correction de la syntaxe dans InfoMachineUpdateDTO

### 🔧 Améliorations techniques

- **Refactoring** : Migration des contrôleurs vers l'utilisation des DTOs pour les requêtes/réponses
- **Documentation API** : Amélioration de la documentation pour forum, machine, message et gestion utilisateur
- **Tests** : Refactoring et amélioration des fonctionnalités de test
- **Types TypeScript** : Nouveaux types pour forum, machine, message et gestion utilisateur

### 🚀 Infrastructure

- **Commandes de diagnostic** : Ajout de commandes pour les performances et informations utilisateur
- **Recherche de logs** : Implémentation de la fonctionnalité de recherche dans les logs
- **Routes** : Mise à jour des chemins de route pour MaterielInfo et EditMachine

---

## [1.1.0] - 2025-07-30

### ✨ Nouvelles fonctionnalités

- **Déconnexion** : Implémentation de la fonctionnalité de déconnexion utilisateur
- **Gestion des posts** : Vue MaterielInfo et gestion des posts de forum
- **Navigation** : Amélioration de la navigation avec nouvelles vues

### 🔧 Améliorations

- **Authentification** : Amélioration des exigences d'authentification
- **Discussion** : Mise à jour de la vue discussion avec gestion des messages

---

## [1.0.0] - 2025-07-25

### 🎉 Version initiale

- **Architecture** : Mise en place de l'architecture Symfony 7.3 + Vue.js 3.5
- **Base de données** : Configuration PostgreSQL avec migrations
- **Authentification** : Système JWT complet
- **Frontend** : Interface Vuetify Material Design
- **API REST** : Endpoints de base pour toutes les entités

### 🔐 Sécurité

- **OWASP** : Implémentation des protections OWASP Top 10
- **Validation** : Validation stricte des entrées utilisateur
- **Chiffrement** : Chiffrement AES-256 des données sensibles

### 📊 Monitoring

- **Logs** : Système de logging structuré
- **Métriques** : Collecte des métriques de performance
- **Health checks** : Vérifications de santé automatiques
