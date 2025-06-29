<?php

namespace App\DTO;

use OpenApi\Attributes as OA;

/**
 * DTO pour la réponse de validation de token JWT.
 */
#[OA\Schema(
    schema: 'TokenValidationResponse',
    title: 'Réponse de validation de token',
    description: 'Structure de la réponse lors de la validation d\'un token JWT',
    type: 'object',
    required: ['valid']
)]
readonly class TokenValidationResponseDTO
{
    public function __construct(
        #[OA\Property(
            property: 'valid',
            type: 'boolean',
            description: 'Indique si le token est valide',
            example: true
        )]
        public readonly bool $valid,

        #[OA\Property(
            property: 'token_id',
            type: 'string',
            description: 'Identifiant unique du token JWT',
            nullable: true,
            example: 'jwt_64f5b2c1a8e9f'
        )]
        public readonly ?string $tokenId = null,

        #[OA\Property(
            property: 'user_id',
            type: 'string',
            description: 'Identifiant de l\'utilisateur',
            nullable: true,
            example: '123'
        )]
        public readonly ?string $userId = null,

        #[OA\Property(
            property: 'username',
            type: 'string',
            description: 'Nom d\'utilisateur',
            nullable: true,
            example: 'john.doe'
        )]
        public readonly ?string $username = null,

        #[OA\Property(
            property: 'token_type',
            type: 'string',
            description: 'Type de token (access ou refresh)',
            nullable: true,
            enum: ['access', 'refresh'],
            example: 'access'
        )]
        public readonly ?string $tokenType = null,

        #[OA\Property(
            property: 'issued_at',
            type: 'string',
            description: 'Date et heure d\'émission du token',
            nullable: true,
            format: 'date-time',
            example: '2025-06-29 10:30:00'
        )]
        public readonly ?string $issuedAt = null,

        #[OA\Property(
            property: 'expires_at',
            type: 'string',
            description: 'Date et heure d\'expiration du token',
            nullable: true,
            format: 'date-time',
            example: '2025-06-29 11:30:00'
        )]
        public readonly ?string $expiresAt = null,

        #[OA\Property(
            property: 'roles',
            type: 'array',
            description: 'Rôles de l\'utilisateur',
            items: new OA\Items(type: 'string'),
            nullable: true,
            example: ['ROLE_USER', 'ROLE_ADMIN']
        )]
        public readonly ?array $roles = null,

        #[OA\Property(
            property: 'error',
            type: 'string',
            description: 'Message d\'erreur si le token est invalide',
            nullable: true,
            example: 'Token expired'
        )]
        public readonly ?string $error = null
    ) {}

    /**
     * Crée un DTO pour un token valide.
     *
     * @param array $tokenInfo Informations du token depuis JWTService::getTokenInfo()
     * @return self
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
            return self::valid($tokenInfo);
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
