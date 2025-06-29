<?php

namespace App\DTO;

use OpenApi\Attributes as OA;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(
    schema: 'TokenRefreshRequestDTO',
    title: 'Requête de rafraîchissement de token JWT',
    description: 'DTO pour rafraîchir un token JWT avec un refresh token',
    type: 'object',
    required: ['refreshToken']
)]
final class TokenRefreshRequestDTO
{
    public function __construct(
        #[OA\Property(
            property: 'refreshToken',
            description: 'Le refresh token JWT à utiliser pour générer un nouveau token d\'accès',
            type: 'string',
            minLength: 50,
            maxLength: 2048,
            example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJtdXNjdXNjb3BlLWFwaS...'
        )]
        #[Assert\NotBlank(message: 'Refresh token is required')]
        #[Assert\Type(type: 'string', message: 'Refresh token must be a string')]
        #[Assert\Length(
            min: 50,
            max: 2048,
            minMessage: 'Refresh token must be at least {{ limit }} characters long',
            maxMessage: 'Refresh token cannot be longer than {{ limit }} characters'
        )]
        public readonly string $refreshToken,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            refreshToken: $data['refreshToken'] ?? ''
        );
    }

    public static function fromParameterBag(ParameterBag $data): self
    {
        return new self(
            refreshToken: $data->get('refreshToken') ?? ''
        );
    }

    public function hasValidFormat(): bool
    {
        return !empty($this->refreshToken) && str_contains($this->refreshToken, '.');
    }

    public function toArray(): array
    {
        return [
            'refreshToken' => $this->refreshToken,
        ];
    }
}
