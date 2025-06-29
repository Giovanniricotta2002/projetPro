<?php

namespace App\DTO;

use OpenApi\Attributes as OA;

/**
 * DTO pour la réponse de validation de token JWT.
 */
final readonly class TokenValidationResponseDTO
{
    public function __construct(
        public readonly bool $valid,
        public readonly ?string $tokenId = null,
        public readonly ?string $userId = null,
        public readonly ?string $username = null,
        public readonly ?string $tokenType = null,
        public readonly ?string $issuedAt = null,
        public readonly ?string $expiresAt = null,
        public readonly ?array $roles = null,
        public readonly ?string $error = null,
    ) {
    }

    /**
     * Crée un DTO pour un token valide.
     *
     * @param array $tokenInfo Informations du token depuis JWTService::getTokenInfo()
     */
    public static function valid(array $tokenInfo): self
    {
        return new self(
            valid: true,
            tokenId: $tokenInfo['token_id'] ?? null,
            userId: $tokenInfo['user_id'] ?? null,
            username: $tokenInfo['username'] ?? null,
            tokenType: $tokenInfo['token_type'] ?? null,
            issuedAt: $tokenInfo['issued_at'] ?? null,
            expiresAt: $tokenInfo['expires_at'] ?? null,
            roles: $tokenInfo['roles'] ?? null
        );
    }

    /**
     * Crée un DTO pour un token invalide.
     *
     * @param string $error Message d'erreur
     */
    public static function invalid(string $error): self
    {
        return new self(
            valid: false,
            error: $error
        );
    }

    /**
     * Crée un DTO depuis les informations du JWTService.
     *
     * @param array $tokenInfo Informations du token
     */
    public static function fromTokenInfo(array $tokenInfo): self
    {
        if ($tokenInfo['valid']) {
            return self::valid($tokenInfo);
        }

        return self::invalid($tokenInfo['error'] ?? 'Invalid token');
    }

    /**
     * Convertit le DTO en tableau associatif.
     */
    public function toArray(): array
    {
        $data = ['valid' => $this->valid];

        if ($this->valid) {
            // Pour un token valide, inclure toutes les informations non-nulles
            $fields = [
                'token_id' => $this->tokenId,
                'user_id' => $this->userId,
                'username' => $this->username,
                'token_type' => $this->tokenType,
                'issued_at' => $this->issuedAt,
                'expires_at' => $this->expiresAt,
                'roles' => $this->roles,
            ];

            foreach ($fields as $key => $value) {
                if ($value !== null) {
                    $data[$key] = $value;
                }
            }
        } else {
            // Pour un token invalide, inclure l'erreur
            if ($this->error !== null) {
                $data['error'] = $this->error;
            }
        }

        return $data;
    }
}
