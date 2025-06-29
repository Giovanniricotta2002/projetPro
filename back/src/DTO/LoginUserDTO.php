<?php

namespace App\DTO;

use OpenApi\Attributes as OA;

/**
 * DTO pour les informations utilisateur dans la réponse de connexion.
 */
#[OA\Schema(
    title: 'Informations utilisateur',
    description: 'Données utilisateur dans les réponses d\'authentification',
    type: 'object',
    required: ['id', 'username', 'roles']
)]
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
        #[OA\Property(property: 'id', type: 'integer', description: 'Identifiant unique de l\'utilisateur', example: 1)]
        public int $id,
        
        #[OA\Property(property: 'username', type: 'string', description: 'Nom d\'utilisateur', example: 'john.doe')]
        public string $username,
        
        #[OA\Property(
            property: 'roles',
            type: 'array',
            items: new OA\Items(type: 'string'),
            description: 'Liste des rôles de l\'utilisateur',
            example: ['ROLE_USER']
        )]
        public array $roles,
        
        #[OA\Property(
            property: 'last_visit',
            type: 'string',
            format: 'date-time',
            nullable: true,
            description: 'Date et heure de la dernière visite',
            example: '2025-06-29 14:30:00'
        )]
        public ?string $lastVisit = null,
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
