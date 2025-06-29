<?php

namespace App\DTO;

/**
 * DTO pour les informations utilisateur dans la réponse de connexion.
 */
final readonly class LoginUserDTO
{
    /**
     * Constructeur du DTO utilisateur.
     *
     * @param int         $id        Identifiant de l'utilisateur
     * @param string      $username  Nom d'utilisateur
     * @param array       $roles     Rôles de l'utilisateur
     * @param string|null $lastVisit Dernière visite formatée
     */
    public function __construct(
        public readonly int $id,
        public readonly string $username,
        public readonly array $roles,
        public readonly ?string $lastVisit = null,
    ) {
    }

    /**
     * Convertit le DTO en tableau pour la sérialisation JSON.
     */
    public function toArray(): array
    {
        $result = [
            'id' => $this->id,
            'username' => $this->username,
            'roles' => $this->roles,
        ];

        if ($this->lastVisit !== null) {
            $result['last_visit'] = $this->lastVisit;
        }

        return $result;
    }
}
