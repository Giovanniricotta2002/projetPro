<?php

namespace App\DTO;

/**
 * DTO pour la réponse d'informations de token JWT.
 * Utilisé pour l'endpoint /api/token/info.
 */
final readonly class TokenInfoResponseDTO
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
        public readonly ?string $issuer = null,
        public readonly ?string $audience = null,
        public readonly ?int $loginTime = null,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null,
        public readonly ?string $error = null,
    ) {
    }

    /**
     * Crée un DTO pour un token valide avec informations complètes.
     *
     * @param array $tokenPayload Payload décodé du token JWT
     */
    public static function validWithDetails(array $tokenPayload): self
    {
        return new self(
            valid: true,
            tokenId: $tokenPayload['jti'] ?? null,
            userId: $tokenPayload['sub'] ?? null,
            username: $tokenPayload['username'] ?? null,
            tokenType: $tokenPayload['token_type'] ?? null,
            issuedAt: isset($tokenPayload['iat']) ? date('Y-m-d H:i:s', $tokenPayload['iat']) : null,
            expiresAt: isset($tokenPayload['exp']) ? date('Y-m-d H:i:s', $tokenPayload['exp']) : null,
            roles: $tokenPayload['roles'] ?? null,
            issuer: $tokenPayload['iss'] ?? null,
            audience: $tokenPayload['aud'] ?? null,
            loginTime: $tokenPayload['login_time'] ?? null,
            ipAddress: $tokenPayload['ip_address'] ?? null,
            userAgent: $tokenPayload['user_agent'] ?? null
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
            // Créer avec les informations disponibles
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
                'issuer' => $this->issuer,
                'audience' => $this->audience,
                'login_time' => $this->loginTime,
                'ip_address' => $this->ipAddress,
                'user_agent' => $this->userAgent,
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

    /**
     * Vérifie si le token est encore valide (non expiré).
     */
    public function isStillValid(): bool
    {
        if (!$this->valid || !$this->expiresAt) {
            return false;
        }

        $expirationTime = strtotime($this->expiresAt);

        return $expirationTime > time();
    }

    /**
     * Obtient le temps restant avant expiration en secondes.
     *
     * @return int|null Secondes restantes ou null si invalide/pas d'expiration
     */
    public function getTimeUntilExpiration(): ?int
    {
        if (!$this->valid || !$this->expiresAt) {
            return null;
        }

        $expirationTime = strtotime($this->expiresAt);
        $remaining = $expirationTime - time();

        return max(0, $remaining);
    }
}
