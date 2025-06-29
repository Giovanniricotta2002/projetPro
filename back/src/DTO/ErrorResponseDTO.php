<?php

namespace App\DTO;

use OpenApi\Attributes as OA;

/**
 * DTO pour les réponses d'erreur standardisées de l'API.
 *
 * Utilisé pour documenter et structurer les réponses d'erreur
 * de façon cohérente à travers toute l'application.
 */
#[OA\Schema(
    schema: 'ErrorResponse',
    title: 'Réponse d\'erreur',
    description: 'Structure standardisée pour les réponses d\'erreur de l\'API',
    type: 'object',
    required: ['error']
)]
final readonly class ErrorResponseDTO
{
    /**
     * Constructeur du DTO d'erreur.
     *
     * @param string      $error   Message d'erreur principal
     * @param string|null $message Message d'erreur détaillé (optionnel)
     * @param int|null    $code    Code d'erreur spécifique (optionnel)
     * @param array|null  $details Détails supplémentaires sur l'erreur (optionnel)
     */
    public function __construct(
        #[OA\Property(
            property: 'error',
            description: 'Message d\'erreur principal',
            type: 'string',
            example: 'Invalid credentials'
        )]
        public readonly string $error,
        #[OA\Property(
            property: 'message',
            description: 'Message d\'erreur détaillé',
            type: 'string',
            nullable: true,
            example: 'The provided username or password is incorrect'
        )]
        public readonly ?string $message = null,
        #[OA\Property(
            property: 'code',
            description: 'Code d\'erreur spécifique à l\'application',
            type: 'integer',
            nullable: true,
            example: 4001
        )]
        public readonly ?int $code = null,
        #[OA\Property(
            property: 'details',
            description: 'Détails supplémentaires sur l\'erreur',
            type: 'object',
            nullable: true
        )]
        public readonly ?array $details = null,
    ) {
    }

    /**
     * Factory method pour créer une réponse d'erreur simple.
     *
     * @param string $error Message d'erreur
     */
    public static function create(string $error): self
    {
        return new self($error);
    }

    /**
     * Factory method pour créer une réponse d'erreur avec message détaillé.
     *
     * @param string $error   Message d'erreur principal
     * @param string $message Message détaillé
     */
    public static function withMessage(string $error, string $message): self
    {
        return new self($error, $message);
    }

    /**
     * Factory method pour créer une réponse d'erreur avec code.
     *
     * @param string $error Message d'erreur
     * @param int    $code  Code d'erreur
     */
    public static function withCode(string $error, int $code): self
    {
        return new self($error, null, $code);
    }

    /**
     * Factory method pour créer une réponse d'erreur complète.
     *
     * @param string $error   Message d'erreur principal
     * @param string $message Message détaillé
     * @param int    $code    Code d'erreur
     * @param array  $details Détails supplémentaires
     */
    public static function full(string $error, string $message, int $code, array $details = []): self
    {
        return new self($error, $message, $code, $details);
    }

    /**
     * Convertit le DTO en tableau pour la sérialisation JSON.
     */
    public function toArray(): array
    {
        $result = ['error' => $this->error];

        if ($this->message !== null) {
            $result['message'] = $this->message;
        }

        if ($this->code !== null) {
            $result['code'] = $this->code;
        }

        if ($this->details !== null) {
            $result['details'] = $this->details;
        }

        return $result;
    }
}
