<?php

namespace App\DTO;

use OpenApi\Attributes as OA;

/**
 * DTO pour la réponse de refresh token.
 */
#[OA\Schema(
    schema: 'TokenRefreshResponse',
    title: 'Réponse de refresh token',
    description: 'Structure de la réponse lors du rafraîchissement d\'un token JWT',
    type: 'object',
    required: ['success', 'message', 'tokens']
)]
readonly class TokenRefreshResponseDTO
{
    public function __construct(
        #[OA\Property(
            property: 'success',
            type: 'boolean',
            description: 'Indicateur de succès du rafraîchissement',
            example: true
        )]
        public readonly bool $success,

        #[OA\Property(
            property: 'message',
            type: 'string',
            description: 'Message de confirmation du rafraîchissement',
            example: 'Token refreshed successfully'
        )]
        public readonly string $message,

        #[OA\Property(
            property: 'tokens',
            description: 'Nouveaux tokens JWT',
            ref: '#/components/schemas/JWTTokens'
        )]
        public readonly JWTTokensDTO $tokens
    ) {}

    /**
     * Crée une réponse de succès pour le refresh.
     *
     * @param JWTTokensDTO $tokens Les nouveaux tokens
     * @param string $message Message personnalisé
     * @return self
     */
    public static function success(
        JWTTokensDTO $tokens,
        string $message = 'Token refreshed successfully'
    ): self {
        return new self(
            success: true,
            message: $message,
            tokens: $tokens
        );
    }

    /**
     * Convertit le DTO en tableau associatif.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'tokens' => $this->tokens->toArray(),
        ];
    }
}
