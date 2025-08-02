# ADR-015: Migration vers Architecture Event-Driven

## Status: ACCEPTED

## Date: 2025-08-02

### Context

L'application monolithique atteint ses limites en termes de scalabilité.
Les opérations CRUD bloquent l'interface utilisateur lors de pics de charge.

### Decision

Implémentation progressive d'une architecture event-driven avec:

- Event Sourcing pour audit trail complet
- CQRS pour séparation lecture/écriture  
- Message Bus asynchrone (RabbitMQ)

### Consequences

**Positive:**

- Meilleure scalabilité horizontale
- Audit trail complet des actions utilisateur
- Résilience améliorée face aux pannes

**Negative:**

- Complexité accrue du débogage
- Courbe d'apprentissage équipe
- Infrastructure additionnelle à maintenir

### Implementation Plan

- Phase 1: Event Bus pour notifications (2 semaines)
- Phase 2: CQRS module utilisateurs (4 semaines)  
- Phase 3: Event Sourcing audit complet (6 semaines)

### References

- [Event Sourcing Pattern](https://martinfowler.com/eaaDev/EventSourcing.html)
- [CQRS Pattern](https://docs.microsoft.com/en-us/azure/architecture/patterns/cqrs)
- [Symfony Messenger Component](https://symfony.com/doc/current/messenger.html)

### Related ADRs

- ADR-001: Architecture initiale monolithique
- ADR-012: Choix de la stack technique Symfony/Vue.js
