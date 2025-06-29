<?php

namespace App\DTO;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO pour les requêtes de refresh token.
 */
#[OA\Schema(
    schema: 'TokenRefreshRequest',
    title: 'Requête de refresh token',
    description: 'Structure de la requête pour rafraîchir un token JWT',
    type: 'object',
    required: ['refresh_token']
)]
readonly class TokenRefreshRequestDTO
{
    public function __construct(
        #[OA\Property(
            property: 'refresh_token',
            type: 'string',
            description: 'Le refresh token JWT valide à utiliser pour générer de nouveaux tokens',
            minLength: 50,
            maxLength: 2048,
            example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJtdXNjdXNjb3BlLWFwaSIsImF1ZCI6Im11c2N1c2NvcGUtdXNlcnMiLCJpYXQiOjE3MTk2NTY0MDAsImV4cCI6MTcyMjI0ODQwMCwibmJmIjoxNzE5NjU2NDAwLCJqdGkiOiJyZWZyZXNoXzY0ZjViMmMxYThlOWYiLCJzdWIiOiIxMjMiLCJ1c2VybmFtZSI6ImpvaG4uZG9lIiwidG9rZW5fdHlwZSI6InJlZnJlc2giLCJjcmVhdGVkX2F0IjoxNzE5NjU2NDAwfQ.signature'
        )]
        #[Assert\NotBlank(message: 'Refresh token is required')]
        #[Assert\Length(
            min: 50,
            max: 2048,
            minMessage: 'Refresh token is too short',
            maxMessage: 'Refresh token is too long'
        )]
        public readonly string $refreshToken
    ) {}

    /**
     * Crée un DTO depuis un tableau de données.
     *
     * @param array $data Données de la requête
     * @return self
     * @throws \InvalidArgumentException Si le refresh_token est manquant
     */
    public static function fromArray(array $data): self
    {
        if (!isset($data['refresh_token']) || empty($data['refresh_token'])) {
            throw new \InvalidArgumentException('Missing refresh_token parameter');
        }

        return new self(
            refreshToken: trim($data['refresh_token'])
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
            'refresh_token' => $this->refreshToken,
        ];
    }

    /**
     * Valide le format basique du refresh token.
     *
     * @return bool True si le format semble valide
     */
    public function hasValidFormat(): bool
    {
        // Vérifier que c'est un JWT (3 parties séparées par des points)
        $parts = explode('.', $this->refreshToken);
        
        return count($parts) === 3 &&
               !empty($parts[0]) &&
               !empty($parts[1]) &&
               !empty($parts[2]);
    }
}

/**
 * DTO pour les requêtes de validation de token.
 */
#[OA\Schema(
    schema: 'TokenValidationRequest',
    title: 'Requête de validation token',
    description: 'Structure de la requête pour valider un token JWT',
    type: 'object',
    required: ['token']
)]
readonly class TokenValidationRequestDTO
{
    public function __construct(
        #[OA\Property(
            property: 'token',
            type: 'string',
            description: 'Le token JWT à valider (access ou refresh token)',
            minLength: 50,
            maxLength: 2048,
            example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJtdXNjdXNjb3BlLWFwaSIsImF1ZCI6Im11c2N1c2NvcGUtdXNlcnMiLCJpYXQiOjE3MTk2NTY0MDAsImV4cCI6MTcxOTY2MDAwMCwibmJmIjoxNzE5NjU2NDAwLCJqdGkiOiJqd3RfNjRmNWIyYzFhOGU5ZiIsInN1YiI6IjEyMyIsInVzZXJuYW1lIjoiam9obi5kb2UiLCJyb2xlcyI6WyJST0xFX1VTRVIiXSwidG9rZW5fdHlwZSI6ImFjY2VzcyIsImxvZ2luX3RpbWUiOjE3MTk2NTY0MDB9.signature'
        )]
        #[Assert\NotBlank(message: 'Token is required')]
        #[Assert\Length(
            min: 50,
            max: 2048,
            minMessage: 'Token is too short',
            maxMessage: 'Token is too long'
        )]
        public readonly string $token
    ) {}

    /**
     * Crée un DTO depuis un tableau de données.
     *
     * @param array $data Données de la requête
     * @return self
     * @throws \InvalidArgumentException Si le token est manquant
     */
    public static function fromArray(array $data): self
    {
        if (!isset($data['token']) || empty($data['token'])) {
            throw new \InvalidArgumentException('Missing token parameter');
        }

        return new self(
            token: trim($data['token'])
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
            'token' => $this->token,
        ];
    }

    /**
     * Valide le format basique du token.
     *
     * @return bool True si le format semble valide
     */
    public function hasValidFormat(): bool
    {
        // Vérifier que c'est un JWT (3 parties séparées par des points)
        $parts = explode('.', $this->token);
        
        return count($parts) === 3 &&
               !empty($parts[0]) &&
               !empty($parts[1]) &&
               !empty($parts[2]);
    }

    /**
     * Extrait le token depuis un header Authorization Bearer.
     *
     * @param string|null $authorizationHeader Le header Authorization
     * @return self|null DTO créé ou null si header invalide
     */
    public static function fromAuthorizationHeader(?string $authorizationHeader): ?self
    {
        if (!$authorizationHeader || !str_starts_with($authorizationHeader, 'Bearer ')) {
            return null;
        }

        $token = substr($authorizationHeader, 7);
        
        if (empty($token)) {
            return null;
        }

        return new self(token: trim($token));
    }
}
