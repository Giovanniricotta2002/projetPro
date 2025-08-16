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
    public ?MessageUserResponseDTO $utilisateur;

    public function __construct(
        int $id,
        string $text,
        ?string $dateCreation,
        ?string $dateModification,
        bool $visible,
        ?MessageUserResponseDTO $utilisateur,
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
        $dateCreation = $message->getDateCreation();
        $dateModification = $message->getDateModification();
        $utilisateur = $message->getUtilisateur();

        return new self(
            $message->getId(),
            $message->getText(),
            $dateCreation ? $dateCreation->format('Y-m-d H:i:s') : '',
            $dateModification ? $dateModification->format('Y-m-d H:i:s') : '',
            $message->isVisible(),
            $utilisateur ? MessageUserResponseDTO::fromEntity($utilisateur) : null,
        );
    }
}
