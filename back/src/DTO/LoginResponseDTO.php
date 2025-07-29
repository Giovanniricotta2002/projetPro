<?php

namespace App\DTO;

/**
 * DTO pour la réponse de connexion réussie.
 */
final readonly class LoginResponseDTO
{
    /**
     * Constructeur du DTO de succès de connexion.
     *
     * @param bool         $success Indicateur de succès
     * @param string       $message Message de succès
     * @param LoginUserDTO $user    Informations utilisateur
     */
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly LoginUserDTO $user,
    ) {
    }

    /**
     * Convertit le DTO en tableau pour la sérialisation JSON.
     *
     * @return array{message: string, success: bool, user: array}
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'user' => $this->user->toArray(),
        ];
    }
}
