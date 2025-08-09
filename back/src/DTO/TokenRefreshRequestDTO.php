<?php

namespace App\DTO;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Validator\Constraints as Assert;

final class TokenRefreshRequestDTO
{
    public function __construct(
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

    /**
     * Summary of fromArray.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            refreshToken: $data['refreshToken'] ?? ''
        );
    }

    public static function fromParameterBag(ParameterBag $data): self
    {
        return new self(
            refreshToken: $data->has('refreshToken') ? $data->get('refreshToken') : ''
        );
    }

    public function hasValidFormat(): bool
    {
        return !empty($this->refreshToken) && str_contains($this->refreshToken, '.');
    }

    /**
     * Summary of toArray.
     *
     * @return array{refreshToken: string}
     */
    public function toArray(): array
    {
        return [
            'refreshToken' => $this->refreshToken,
        ];
    }
}
