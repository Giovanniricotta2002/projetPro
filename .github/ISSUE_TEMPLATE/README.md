# 📋 GitHub Issue Templates - MuscuScope

Ce dossier contient les templates standardisés pour les issues GitHub de MuscuScope.

## 🐛 Template de Fiche d'Anomalie

Le fichier `bug_report.yml` fournit un formulaire structuré pour le signalement d'anomalies conforme au plan de correction des bogues.

### Utilisation

1. **Création d'une nouvelle issue** : Allez sur l'onglet "Issues" du repository
2. **Sélection du template** : Cliquez sur "New issue" puis choisissez "🐛 Fiche d'Anomalie Standard"
3. **Remplissage du formulaire** : Complétez tous les champs requis selon les instructions

### Structure du Template

Le template suit la structure de la fiche d'anomalie standard définie dans `PLAN_CORRECTION_BOGUES.md` :

#### 📊 Classification Automatique
- **Niveau de criticité** : Critique, Majeur, Mineur, Cosmétique
- **Type d'anomalie** : Fonctionnelle, Performance, Sécurité, Interface, Données

#### 🔍 Informations Techniques
- Version de l'application
- Environnement (navigateur, OS)
- URL concernée
- Données techniques (stack trace, logs)

#### 📝 Description Détaillée
- Description claire de l'anomalie
- Étapes de reproduction
- Comportement attendu vs observé
- Impact sur les utilisateurs

#### 📎 Pièces Jointes
- Captures d'écran
- Logs d'erreur
- Vidéos de reproduction
- Exports réseau

### Labels Automatiques

Le template applique automatiquement :
- `bug` : Pour identifier comme anomalie
- `needs-triage` : Pour signaler qu'un triage est nécessaire

### Workflow Post-Création

1. **Triage automatique** : Classification selon la criticité
2. **Assignation** : Tech Lead assigne selon la charge
3. **Priorisation** : Intégration dans le sprint selon SLA
4. **Suivi** : Utilisation des commentaires pour les mises à jour

## 🔧 Personnalisation

Pour modifier le template :

1. Éditez le fichier `bug_report.yml`
2. Respectez la syntaxe YAML des GitHub Issue Forms
3. Testez sur une issue de test avant publication

## 📚 Documentation Associée

- `PLAN_CORRECTION_BOGUES.md` : Plan complet de correction
- `STRATEGIE_TESTS.md` : Stratégie de tests et validation
- `DOCUMENTATION_EXPLOITATION.md` : Procédures opérationnelles

---

**💡 Ce template standardise le processus de signalement d'anomalies et améliore la qualité des informations collectées pour un debugging plus efficace.**
