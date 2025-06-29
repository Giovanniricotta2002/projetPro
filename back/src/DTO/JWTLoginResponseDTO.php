<?php

namespace App\DTO;

use OpenApi\Attributes as OA;

/**
 * DTO pour la réponse JWT d'authentification réussie.
 */
#[OA\Schema(
    schema: 'JWTLoginResponse',
    title: 'Réponse de connexion JWT',
    description: 'Structure de la réponse lors d\'une authentification JWT réussie',
    type: 'object',
    required: ['success', 'message', 'user', 'tokens']
)]
readonly class JWTLoginResponseDTO
{
    /**
     * Constructeur du DTO de succès de connexion JWT.
     *
     * @param bool $success Indicateur de succès
     * @param string $message Message de succès
     * @param LoginUserDTO $user Informations utilisateur
     * @param array $tokens Tokens JWT (access + refresh)
     */
    public function __construct(
        #[OA\Property(
            property: 'success',
            description: 'Indicateur de succès de la connexion',
            type: 'boolean',
            example: true
        )]
        public readonly bool $success,
        
        #[OA\Property(
            property: 'message',
            description: 'Message de confirmation',
            type: 'string',
            example: 'Login successful'
        )]
        public readonly string $message,
        
        #[OA\Property(
            property: 'user',
            description: 'Informations de l\'utilisateur connecté',
            ref: '#/components/schemas/LoginUser'
        )]
        public readonly LoginUserDTO $user,
        
        #[OA\Property(
            property: 'tokens',
            description: 'Tokens JWT d\'authentification',
            ref: '#/components/schemas/JWTTokens'
        )]
        public readonly JWTTokensDTO $tokens
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
            'tokens' => $this->tokens->toArray(),
        ];
    }

    /**
     * Crée un DTO de succès avec tokens JWT.
     *
     * @param LoginUserDTO $user Informations utilisateur
     * @param JWTTokensDTO $tokens Tokens JWT
     * @param string $message Message personnalisé
     * @return self
     */
    public static function success(
        LoginUserDTO $user, 
        JWTTokensDTO $tokens, 
        string $message = 'Login successful'
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
     * @return bool True si la structure est valide
     */
    public static function validateTokensStructure(JWTTokensDTO $tokens): bool
    {
        return $tokens->isValid();
    }
}
