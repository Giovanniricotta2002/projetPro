<?php

namespace App\Enum;

/**
 * Énumération des statuts possibles d'un utilisateur.
 * 
 * Définit les différents états dans lesquels peut se trouver un compte utilisateur
 * pour gérer l'accès et les permissions de manière granulaire.
 */
enum UserStatus: string
{
    /**
     * Utilisateur actif - peut se connecter et utiliser l'application normalement.
     */
    case ACTIVE = 'active';

    /**
     * Utilisateur inactif - compte désactivé temporairement ou par choix.
     */
    case INACTIVE = 'inactive';

    /**
     * Utilisateur suspendu - compte bloqué par un administrateur pour violation.
     */
    case SUSPENDED = 'suspended';

    /**
     * Utilisateur en attente - compte créé mais non encore validé (email, etc.).
     */
    case PENDING = 'pending';

    /**
     * Utilisateur banni - compte définitivement bloqué.
     */
    case BANNED = 'banned';

    /**
     * Compte supprimé - marqué pour suppression mais conservé pour historique.
     */
    case DELETED = 'deleted';

    /**
     * Retourne le libellé français du statut.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::ACTIVE => 'Actif',
            self::INACTIVE => 'Inactif',
            self::SUSPENDED => 'Suspendu',
            self::PENDING => 'En attente',
            self::BANNED => 'Banni',
            self::DELETED => 'Supprimé',
        };
    }

    /**
     * Retourne la description du statut.
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::ACTIVE => 'L\'utilisateur peut se connecter et utiliser l\'application',
            self::INACTIVE => 'L\'utilisateur ne peut pas se connecter, compte désactivé',
            self::SUSPENDED => 'L\'utilisateur est temporairement bloqué par un administrateur',
            self::PENDING => 'L\'utilisateur doit valider son compte (email, etc.)',
            self::BANNED => 'L\'utilisateur est définitivement banni de l\'application',
            self::DELETED => 'Le compte a été supprimé mais conservé pour l\'historique',
        };
    }

    /**
     * Vérifie si l'utilisateur peut se connecter avec ce statut.
     */
    public function canLogin(): bool
    {
        return match ($this) {
            self::ACTIVE => true,
            self::INACTIVE, 
            self::SUSPENDED, 
            self::PENDING, 
            self::BANNED, 
            self::DELETED => false,
        };
    }

    /**
     * Vérifie si le statut permet l'accès à l'application.
     */
    public function isAccessible(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Vérifie si le statut nécessite une action de l'utilisateur.
     */
    public function requiresUserAction(): bool
    {
        return $this === self::PENDING;
    }

    /**
     * Vérifie si le statut est temporaire (peut être changé facilement).
     */
    public function isTemporary(): bool
    {
        return match ($this) {
            self::INACTIVE, 
            self::SUSPENDED, 
            self::PENDING => true,
            self::ACTIVE, 
            self::BANNED, 
            self::DELETED => false,
        };
    }

    /**
     * Retourne la couleur associée au statut (pour l'interface).
     */
    public function getColor(): string
    {
        return match ($this) {
            self::ACTIVE => 'green',
            self::INACTIVE => 'gray',
            self::SUSPENDED => 'orange',
            self::PENDING => 'blue',
            self::BANNED => 'red',
            self::DELETED => 'black',
        };
    }

    /**
     * Retourne tous les statuts sous forme de tableau associatif.
     * 
     * @return array<string, string> [value => label]
     */
    public static function getChoices(): array
    {
        $choices = [];
        foreach (self::cases() as $case) {
            $choices[$case->value] = $case->getLabel();
        }
        return $choices;
    }

    /**
     * Retourne les statuts qui permettent la connexion.
     * 
     * @return array<UserStatus>
     */
    public static function getLoginAllowed(): array
    {
        return array_filter(
            self::cases(),
            fn(UserStatus $status) => $status->canLogin()
        );
    }

    /**
     * Retourne les statuts actifs (non supprimés/bannis).
     * 
     * @return array<UserStatus>
     */
    public static function getActiveStatuses(): array
    {
        return [
            self::ACTIVE,
            self::INACTIVE,
            self::SUSPENDED,
            self::PENDING,
        ];
    }

    /**
     * Créer depuis une chaîne de caractères avec validation.
     * 
     * @throws \InvalidArgumentException Si le statut n'existe pas
     */
    public static function fromString(string $value): self
    {
        $status = self::tryFrom($value);
        if ($status === null) {
            throw new \InvalidArgumentException("Invalid user status: {$value}");
        }
        return $status;
    }
}
