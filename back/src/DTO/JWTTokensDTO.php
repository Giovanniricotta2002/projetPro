<?php

namespace App\DTO;

/**
 * DTO pour les tokens JWT (access + refresh).
 */
final readonly class JWTTokensDTO
{
    public function __construct(
        public readonly string $accessToken,
        public readonly string $refreshToken,
        public readonly string $tokenType,
        public readonly int $expiresIn,
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
