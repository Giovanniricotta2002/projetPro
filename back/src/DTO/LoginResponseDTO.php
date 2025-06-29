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
final readonly class LoginResponseDTO
{
    /**
     * Constructeur du DTO de succès de connexion.
     *
     * @param bool         $success Indicateur de succès
     * @param string       $message Message de succès
     * @param LoginUserDTO $user    Informations utilisateur
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
        public readonly LoginUserDTO $user,
    ) {
    }

    /**
     * Convertit le DTO en tableau pour la sérialisation JSON.
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'user' => $this->user->toArray(),
        ];
    }
}
