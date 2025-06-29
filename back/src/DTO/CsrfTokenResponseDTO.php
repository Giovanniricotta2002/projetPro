<?php

namespace App\DTO;

use OpenApi\Attributes as OA;

/**
 * DTO pour la réponse de génération de token CSRF.
 */
#[OA\Schema(
    title: 'Réponse de génération de token CSRF',
    description: 'Structure de la réponse lors de la génération d\'un token CSRF',
    type: 'object',
    required: ['csrfToken']
)]
final readonly class CsrfTokenResponseDTO
{
    /**
     * Constructeur du DTO de token CSRF.
     *
     * @param string $csrfToken Le token CSRF généré
     */
    public function __construct(
        #[OA\Property(
            property: 'csrfToken',
            description: 'Le token CSRF généré',
            type: 'string',
            example: 'abc123def456ghi789'
        )]
        public readonly string $csrfToken,
    ) {
    }

    /**
     * Factory method pour créer une réponse de token CSRF.
     *
     * @param string $token Le token CSRF
     */
    public static function create(string $token): self
    {
        return new self($token);
    }

    /**
     * Convertit le DTO en tableau pour la sérialisation JSON.
     */
    public function toArray(): array
    {
        return [
            'csrfToken' => $this->csrfToken,
        ];
    }
}
