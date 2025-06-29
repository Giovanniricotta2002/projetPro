<?php

namespace App\DTO;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO pour les requêtes de token.
 */
#[OA\Schema(
    schema: 'TokenRequest',
    title: 'Requête de token',
    description: 'Structure de la requête pour obtenir un token JWT',
    type: 'object',
    required: ['refresh_token']
)]
final readonly class TokenRequestDTO
{
    public function __construct(
        #[OA\Property(
            property: 'refresh_token',
            type: 'string',
            description: 'Le refresh token JWT valide à utiliser pour générer de nouveaux tokens',
            minLength: 50,
            maxLength: 2048,
            example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJtdXNjdXNjb3BlLWFwaSJ9.signature'
        )]
        #[Assert\NotBlank(message: 'Refresh token is required')]
        #[Assert\Length(
            min: 50,
            max: 2048,
            minMessage: 'Refresh token is too short',
            maxMessage: 'Refresh token is too long'
        )]
        public readonly string $refreshToken,
    ) {
    }

    /**
     * Crée un DTO depuis un tableau de données.
     *
     * @param array $data Données de la requête
     *
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

        return count($parts) === 3
               && !empty($parts[0])
               && !empty($parts[1])
               && !empty($parts[2]);
    }
}
