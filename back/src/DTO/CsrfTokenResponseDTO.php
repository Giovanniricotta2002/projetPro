<?php

namespace App\DTO;

/**
 * DTO pour la réponse de génération de token CSRF.
 */
final readonly class CsrfTokenResponseDTO
{
    /**
     * Constructeur du DTO de token CSRF.
     *
     * @param string $csrfToken Le token CSRF généré
     */
    public function __construct(
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
