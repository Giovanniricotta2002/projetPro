<?php

namespace App\DTO;

/**
 * DTO pour la réponse de refresh token.
 */
final readonly class TokenRefreshResponseDTO
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly JWTTokensDTO $tokens,
    ) {
    }

    /**
     * Crée une réponse de succès pour le refresh.
     *
     * @param JWTTokensDTO $tokens  Les nouveaux tokens
     * @param string       $message Message personnalisé
     */
    public static function success(
        JWTTokensDTO $tokens,
        string $message = 'Token refreshed successfully',
    ): self {
        return new self(
            success: true,
            message: $message,
            tokens: $tokens
        );
    }

    /**
     * Convertit le DTO en tableau associatif.
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
