<?php

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CsrfTokenVerificationResponse',
    title: 'Réponse de vérification de token CSRF',
    description: 'Structure de réponse après vérification d\'un token CSRF',
    type: 'object',
    required: ['valid', 'message']
)]
class CsrfTokenVerificationResponseDTO
{
    public function __construct(
        #[OA\Property(
            property: 'valid',
            type: 'boolean',
            description: 'Indique si le token CSRF est valide',
            example: true
        )]
        public readonly bool $valid,

        #[OA\Property(
            property: 'message',
            type: 'string',
            description: 'Message descriptif du résultat de la vérification',
            example: 'Token CSRF valide'
        )]
        public readonly string $message
    ) {}

    public static function createValid(): self
    {
        return new self(true, 'Token CSRF valide');
    }

    public static function createInvalid(): self
    {
        return new self(false, 'Token CSRF invalide');
    }

    public function toArray(): array
    {
        return [
            'valid' => $this->valid,
            'message' => $this->message
        ];
    }
}
