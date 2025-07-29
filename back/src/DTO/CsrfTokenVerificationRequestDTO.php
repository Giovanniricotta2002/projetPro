<?php

namespace App\DTO;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Validator\Constraints as Assert;

final class CsrfTokenVerificationRequestDTO
{
    public function __construct(
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

    /**
     * Summary of fromArray.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            csrfToken: $data['csrfToken'] ?? ''
        );
    }

    public static function fromParameterBag(ParameterBag $data): self
    {
        return new self(
            csrfToken: $data->get('csrfToken')
        );
    }

    /**
     * Summary of toArray.
     *
     * @return array{csrfToken: string}
     */
    public function toArray(): array
    {
        return [
            'csrfToken' => $this->csrfToken,
        ];
    }
}
