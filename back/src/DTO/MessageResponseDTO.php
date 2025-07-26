<?php

namespace App\DTO;

use App\Entity\Message;
use OpenApi\Attributes as OA;

#[OA\Schema(description: 'Message', required: ['id', 'text', 'dateCreation', 'dateModification', 'visible', 'utilisateur'])]
class MessageResponseDTO
{
    #[OA\Property(type: 'integer', example: 1)]
    public int $id;
    #[OA\Property(type: 'string', example: 'Ceci est un message')]
    public string $text;
    #[OA\Property(type: 'string', format: 'date-time', example: '2025-07-27T10:00:00')]
    public string $dateCreation;
    #[OA\Property(type: 'string', format: 'date-time', example: '2025-07-27T10:05:00')]
    public string $dateModification;
    #[OA\Property(type: 'boolean', example: true)]
    public bool $visible;
    #[OA\Property(type: 'object', ref: MessageUserResponseDTO::class)]
    public $utilisateur;

    public function __construct(
        int $id,
        string $text,
        ?string $dateCreation,
        ?string $dateModification,
        bool $visible,
        MessageUserResponseDTO $utilisateur,
    ) {
        $this->id = $id;
        $this->text = $text;
        $this->dateCreation = $dateCreation;
        $this->dateModification = $dateModification;
        $this->visible = $visible;
        $this->utilisateur = $utilisateur;
    }

    public static function fromEntity(Message $message): self
    {
        return new self(
            $message->getId(),
            $message->getText(),
            $message->getDateCreation()?->format('Y-m-d H:i:s'),
            $message->getDateModification()?->format('Y-m-d H:i:s'),
            $message->isVisible(),
            MessageUserResponseDTO::fromEntity($message->getUtilisateur()),
        );
    }
}
