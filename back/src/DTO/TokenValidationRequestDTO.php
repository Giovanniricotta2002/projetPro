<?php

namespace App\DTO;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Validator\Constraints as Assert;

final class TokenValidationRequestDTO
{
    public function __construct(
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

    /**
     * Summary of fromArray.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            token: $data['token'] ?? ''
        );
    }

    public static function fromParameterBag(ParameterBag $data): self
    {
        return new self(
            token: $data->get('token')
        );
    }

    public function hasValidFormat(): bool
    {
        return !empty($this->token) && str_contains($this->token, '.');
    }

    /**
     * Summary of toArray.
     *
     * @return array{token: string}
     */
    public function toArray(): array
    {
        return [
            'token' => $this->token,
        ];
    }
}
