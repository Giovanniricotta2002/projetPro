<?php

namespace App\DTO;

use OpenApi\Attributes as OA;

/**
 * DTO pour la réponse d'informations de token JWT.
 * Utilisé pour l'endpoint /api/token/info
 */
#[OA\Schema(
    schema: 'TokenInfoResponse',
    title: 'Informations du token JWT',
    description: 'Structure détaillée des informations d\'un token JWT depuis l\'Authorization header',
    type: 'object',
    required: ['valid']
)]
readonly class TokenInfoResponseDTO
{
    public function __construct(
        #[OA\Property(
            property: 'valid',
            type: 'boolean',
            description: 'Indique si le token est valide et non expiré',
            example: true
        )]
        public readonly bool $valid,

        #[OA\Property(
            property: 'token_id',
            type: 'string',
            description: 'Identifiant unique du token JWT (jti claim)',
            nullable: true,
            example: 'jwt_64f5b2c1a8e9f'
        )]
        public readonly ?string $tokenId = null,

        #[OA\Property(
            property: 'user_id',
            type: 'string',
            description: 'Identifiant de l\'utilisateur (sub claim)',
            nullable: true,
            example: '123'
        )]
        public readonly ?string $userId = null,

        #[OA\Property(
            property: 'username',
            type: 'string',
            description: 'Nom d\'utilisateur associé au token',
            nullable: true,
            example: 'john.doe'
        )]
        public readonly ?string $username = null,

        #[OA\Property(
            property: 'token_type',
            type: 'string',
            description: 'Type de token JWT',
            nullable: true,
            enum: ['access', 'refresh'],
            example: 'access'
        )]
        public readonly ?string $tokenType = null,

        #[OA\Property(
            property: 'issued_at',
            type: 'string',
            description: 'Date et heure d\'émission du token (iat claim)',
            nullable: true,
            format: 'date-time',
            example: '2025-06-29 10:30:00'
        )]
        public readonly ?string $issuedAt = null,

        #[OA\Property(
            property: 'expires_at',
            type: 'string',
            description: 'Date et heure d\'expiration du token (exp claim)',
            nullable: true,
            format: 'date-time',
            example: '2025-06-29 11:30:00'
        )]
        public readonly ?string $expiresAt = null,

        #[OA\Property(
            property: 'roles',
            type: 'array',
            description: 'Rôles et permissions de l\'utilisateur',
            items: new OA\Items(type: 'string'),
            nullable: true,
            example: ['ROLE_USER', 'ROLE_ADMIN']
        )]
        public readonly ?array $roles = null,

        #[OA\Property(
            property: 'issuer',
            type: 'string',
            description: 'Émetteur du token (iss claim)',
            nullable: true,
            example: 'muscuscope-api'
        )]
        public readonly ?string $issuer = null,

        #[OA\Property(
            property: 'audience',
            type: 'string',
            description: 'Audience du token (aud claim)',
            nullable: true,
            example: 'muscuscope-users'
        )]
        public readonly ?string $audience = null,

        #[OA\Property(
            property: 'login_time',
            type: 'integer',
            description: 'Timestamp de la connexion initiale',
            nullable: true,
            example: 1719656400
        )]
        public readonly ?int $loginTime = null,

        #[OA\Property(
            property: 'ip_address',
            type: 'string',
            description: 'Adresse IP lors de la génération du token',
            nullable: true,
            example: '192.168.1.100'
        )]
        public readonly ?string $ipAddress = null,

        #[OA\Property(
            property: 'user_agent',
            type: 'string',
            description: 'User-Agent lors de la génération du token',
            nullable: true,
            example: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        )]
        public readonly ?string $userAgent = null,

        #[OA\Property(
            property: 'error',
            type: 'string',
            description: 'Message d\'erreur détaillé si le token est invalide',
            nullable: true,
            example: 'Token signature verification failed'
        )]
        public readonly ?string $error = null
    ) {}

    /**
     * Crée un DTO pour un token valide avec informations complètes.
     *
     * @param array $tokenPayload Payload décodé du token JWT
     * @return self
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
     * @return self
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
     * @return self
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
     *
     * @return array
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
     *
     * @return bool
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
