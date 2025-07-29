<?php

namespace App\DTO;

/**
 * DTO pour les réponses d'erreur standardisées de l'API.
 *
 * Utilisé pour documenter et structurer les réponses d'erreur
 * de façon cohérente à travers toute l'application.
 */
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
        public readonly string $error,
        public readonly ?string $message = null,
        public readonly ?int $code = null,
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
     *
     * @return array<array|int|null>|array{error: string}
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
