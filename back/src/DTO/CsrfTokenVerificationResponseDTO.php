<?php

namespace App\DTO;

class CsrfTokenVerificationResponseDTO
{
    public function __construct(
        public readonly bool $valid,
        public readonly string $message,
    ) {
    }

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
            'message' => $this->message,
        ];
    }
}
