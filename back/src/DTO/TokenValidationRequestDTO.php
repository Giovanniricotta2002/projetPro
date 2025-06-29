<?php

namespace App\DTO;

use OpenApi\Attributes as OA;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(
    schema: 'TokenValidationRequestDTO',
    title: 'Requête de validation de token JWT',
    description: 'DTO pour valider un token JWT',
    type: 'object',
    required: ['token']
)]
final class TokenValidationRequestDTO
{
    public function __construct(
        #[OA\Property(
            property: 'token',
            description: 'Le token JWT à valider',
            type: 'string',
            minLength: 50,
            maxLength: 2048,
            example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJtdXNjdXNjb3BlLWFwaS...'
        )]
        #[Assert\NotBlank(message: 'Token is required')]
        #[Assert\Type(type: 'string', message: 'Token must be a string')]
        #[Assert\Length(
            min: 50,
            max: 2048,
            minMessage: 'Token must be at least {{ limit }} characters long',
            maxMessage: 'Token cannot be longer than {{ limit }} characters'
        )]
        public readonly string $token,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            token: $data['token'] ?? ''
        );
    }

    public static function fromParameterBag(ParameterBag $data): self
    {
        return new self(
            token: $data->get('token', '')
        );
    }

    public function hasValidFormat(): bool
    {
        return !empty($this->token) && str_contains($this->token, '.');
    }

    public function toArray(): array
    {
        return [
            'token' => $this->token,
        ];
    }
}
