<?php

namespace App\DTO;

use OpenApi\Attributes as OA;

/**
 * DTO pour la réponse de connexion réussie.
 */
#[OA\Schema(
    schema: 'LoginSuccessResponse',
    title: 'Réponse de connexion réussie',
    description: 'Structure de la réponse lors d\'une authentification réussie',
    type: 'object',
    required: ['success', 'message', 'user']
)]
readonly class LoginSuccessResponseDTO
{
    /**
     * Constructeur du DTO de succès de connexion.
     *
     * @param bool $success Indicateur de succès
     * @param string $message Message de succès
     * @param LoginUserDTO $user Informations utilisateur
     */
    public function __construct(
        #[OA\Property(
            property: 'success',
            description: 'Indicateur de succès de la connexion',
            type: 'boolean',
            example: true
        )]
        public readonly bool $success,
        
        #[OA\Property(
            property: 'message',
            description: 'Message de confirmation',
            type: 'string',
            example: 'Login successful'
        )]
        public readonly string $message,
        
        #[OA\Property(
            property: 'user',
            description: 'Informations de l\'utilisateur connecté',
            ref: '#/components/schemas/LoginUser'
        )]
        public readonly LoginUserDTO $user
    ) {
    }

    /**
     * Convertit le DTO en tableau pour la sérialisation JSON.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'user' => $this->user->toArray()
        ];
    }
}

/**
 * DTO pour les informations utilisateur dans la réponse de connexion.
 */
#[OA\Schema(
    schema: 'LoginUser',
    title: 'Utilisateur connecté',
    description: 'Informations de l\'utilisateur lors de la connexion',
    type: 'object',
    required: ['id', 'username', 'roles']
)]
readonly class LoginUserDTO
{
    /**
     * Constructeur du DTO utilisateur.
     *
     * @param int $id Identifiant de l'utilisateur
     * @param string $username Nom d'utilisateur
     * @param array $roles Rôles de l'utilisateur
     * @param string|null $lastVisit Dernière visite formatée
     */
    public function __construct(
        #[OA\Property(
            property: 'id',
            description: 'Identifiant unique de l\'utilisateur',
            type: 'integer',
            example: 123
        )]
        public readonly int $id,
        
        #[OA\Property(
            property: 'username',
            description: 'Nom d\'utilisateur',
            type: 'string',
            example: 'john.doe'
        )]
        public readonly string $username,
        
        #[OA\Property(
            property: 'roles',
            description: 'Rôles de l\'utilisateur',
            type: 'array',
            items: new OA\Items(type: 'string'),
            example: ['ROLE_USER', 'ROLE_ADMIN']
        )]
        public readonly array $roles,
        
        #[OA\Property(
            property: 'last_visit',
            description: 'Date et heure de la dernière visite',
            type: 'string',
            format: 'date-time',
            nullable: true,
            example: '2025-01-15 14:30:00'
        )]
        public readonly ?string $lastVisit = null
    ) {
    }

    /**
     * Convertit le DTO en tableau pour la sérialisation JSON.
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [
            'id' => $this->id,
            'username' => $this->username,
            'roles' => $this->roles
        ];
        
        if ($this->lastVisit !== null) {
            $result['last_visit'] = $this->lastVisit;
        }
        
        return $result;
    }
}
