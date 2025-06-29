<?php

namespace App\DTO;

use OpenApi\Attributes as OA;

/**
 * DTO pour les tokens JWT (access + refresh).
 */
#[OA\Schema(
    schema: 'JWTTokens',
    title: 'Tokens JWT',
    description: 'Structure contenant les tokens JWT (accès et rafraîchissement)',
    type: 'object',
    required: ['access_token', 'refresh_token', 'token_type', 'expires_in']
)]
final readonly class JWTTokensDTO
{
    public function __construct(
        #[OA\Property(
            property: 'access_token',
            type: 'string',
            description: 'Token d\'accès JWT pour l\'authentification',
            example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJtdXNjdXNjb3BlLWFwaSIsImF1ZCI6Im11c2N1c2NvcGUtdXNlcnMiLCJpYXQiOjE3MTk2NTY0MDAsImV4cCI6MTcxOTY2MDAwMCwibmJmIjoxNzE5NjU2NDAwLCJqdGkiOiJqd3RfNjRmNWIyYzFhOGU5ZiIsInN1YiI6IjEyMyIsInVzZXJuYW1lIjoiam9obi5kb2UiLCJyb2xlcyI6WyJST0xFX1VTRVIiXSwidG9rZW5fdHlwZSI6ImFjY2VzcyIsImxvZ2luX3RpbWUiOjE3MTk2NTY0MDB9.signature'
        )]
        public readonly string $accessToken,
        #[OA\Property(
            property: 'refresh_token',
            type: 'string',
            description: 'Token de rafraîchissement JWT pour renouveler l\'accès',
            example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJtdXNjdXNjb3BlLWFwaSIsImF1ZCI6Im11c2N1c2NvcGUtdXNlcnMiLCJpYXQiOjE3MTk2NTY0MDAsImV4cCI6MTcyMjI0ODQwMCwibmJmIjoxNzE5NjU2NDAwLCJqdGkiOiJyZWZyZXNoXzY0ZjViMmMxYThlOWYiLCJzdWIiOiIxMjMiLCJ1c2VybmFtZSI6ImpvaG4uZG9lIiwidG9rZW5fdHlwZSI6InJlZnJlc2giLCJjcmVhdGVkX2F0IjoxNzE5NjU2NDAwfQ.signature'
        )]
        public readonly string $refreshToken,
        #[OA\Property(
            property: 'token_type',
            type: 'string',
            description: 'Type de token (toujours "Bearer" pour JWT)',
            enum: ['Bearer'],
            example: 'Bearer'
        )]
        public readonly string $tokenType,
        #[OA\Property(
            property: 'expires_in',
            type: 'integer',
            description: 'Durée de vie du token d\'accès en secondes',
            minimum: 1,
            example: 3600
        )]
        public readonly int $expiresIn,
        #[OA\Property(
            property: 'refresh_expires_in',
            type: 'integer',
            description: 'Durée de vie du refresh token en secondes',
            minimum: 1,
            example: 2592000
        )]
        public readonly ?int $refreshExpiresIn = null,
    ) {
    }

    /**
     * Crée un DTO depuis le tableau de tokens du JWTService.
     *
     * @param array $tokens Tableau de tokens du JWTService
     */
    public static function fromArray(array $tokens): self
    {
        return new self(
            accessToken: $tokens['access_token'],
            refreshToken: $tokens['refresh_token'],
            tokenType: $tokens['token_type'],
            expiresIn: $tokens['expires_in'],
            refreshExpiresIn: $tokens['refresh_expires_in'] ?? null
        );
    }

    /**
     * Convertit le DTO en tableau associatif.
     */
    public function toArray(): array
    {
        $data = [
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'token_type' => $this->tokenType,
            'expires_in' => $this->expiresIn,
        ];

        if ($this->refreshExpiresIn !== null) {
            $data['refresh_expires_in'] = $this->refreshExpiresIn;
        }

        return $data;
    }

    /**
     * Valide la structure des tokens.
     *
     * @return bool True si la structure est valide
     */
    public function isValid(): bool
    {
        return !empty($this->accessToken)
               && !empty($this->refreshToken)
               && $this->tokenType === 'Bearer'
               && $this->expiresIn > 0;
    }

    /**
     * Obtient uniquement le token d'accès pour les headers Authorization.
     *
     * @return string Le token d'accès avec le préfixe Bearer
     */
    public function getAuthorizationHeader(): string
    {
        return $this->tokenType . ' ' . $this->accessToken;
    }

    /**
     * Crée un DTO pour la réponse de refresh (sans refresh_expires_in).
     *
     * @param array $tokens Tokens du JWTService
     */
    public static function forRefreshResponse(array $tokens): self
    {
        return new self(
            accessToken: $tokens['access_token'],
            refreshToken: $tokens['refresh_token'],
            tokenType: $tokens['token_type'],
            expiresIn: $tokens['expires_in']
        );
    }
}
