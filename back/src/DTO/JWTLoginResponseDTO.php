<?php

namespace App\DTO;

/**
 * DTO pour la réponse JWT d'authentification réussie.
 */
final readonly class JWTLoginResponseDTO
{
    /**
     * Constructeur du DTO de succès de connexion JWT.
     *
     * @param bool         $success Indicateur de succès
     * @param string       $message Message de succès
     * @param LoginUserDTO $user    Informations utilisateur
     * @param array        $tokens  Tokens JWT (access + refresh)
     */
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly LoginUserDTO $user,
        public readonly JWTTokensDTO $tokens,
    ) {
    }

    /**
     * Convertit le DTO en tableau associatif.
     *
     * @return array Le tableau représentant le DTO
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'user' => $this->user->toArray(),
            'tokens' => $this->tokens, // Return as object, not array
        ];
    }

    /**
     * Crée un DTO de succès avec tokens JWT.
     *
     * @param LoginUserDTO $user    Informations utilisateur
     * @param JWTTokensDTO $tokens  Tokens JWT
     * @param string       $message Message personnalisé
     */
    public static function success(
        LoginUserDTO $user,
        JWTTokensDTO $tokens,
        string $message = 'Login successful',
    ): self {
        return new self(
            success: true,
            message: $message,
            user: $user,
            tokens: $tokens
        );
    }

    /**
     * Valide la structure des tokens.
     *
     * @param JWTTokensDTO $tokens Les tokens à valider
     *
     * @return bool True si la structure est valide
     */
    public static function validateTokensStructure(JWTTokensDTO $tokens): bool
    {
        return $tokens->isValid();
    }
}
