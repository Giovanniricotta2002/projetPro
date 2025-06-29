<?php

namespace App\DTO;

use OpenApi\Attributes as OA;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(
    schema: 'CsrfTokenVerificationRequestDTO',
    title: 'Requête de vérification de token CSRF',
    description: 'DTO pour la vérification d\'un token CSRF',
    type: 'object',
    required: ['csrfToken']
)]
final class CsrfTokenVerificationRequestDTO
{
    public function __construct(
        #[OA\Property(
            property: 'csrfToken',
            description: 'Le token CSRF à vérifier',
            type: 'string',
            minLength: 16,
            maxLength: 128,
            pattern: '^[a-zA-Z0-9_-]+$',
            example: 'abc123def456ghi789jkl'
        )]
        #[Assert\NotBlank(message: 'CSRF token is required')]
        #[Assert\Type(type: 'string', message: 'CSRF token must be a string')]
        #[Assert\Length(
            min: 16,
            max: 128,
            minMessage: 'CSRF token must be at least {{ limit }} characters long',
            maxMessage: 'CSRF token cannot be longer than {{ limit }} characters'
        )]
        #[Assert\Regex(
            pattern: '/^[a-zA-Z0-9_-]+$/',
            message: 'CSRF token contains invalid characters'
        )]
        public readonly string $csrfToken,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            csrfToken: $data['csrfToken'] ?? ''
        );
    }

    public static function fromParameterBag(ParameterBag $data): self
    {
        return new self(
            csrfToken: $data->get('csrfToken', '')
        );
    }

    public function toArray(): array
    {
        return [
            'csrfToken' => $this->csrfToken,
        ];
    }
}
