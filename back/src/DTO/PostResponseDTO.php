<?php

namespace App\DTO;

use App\Entity\Message;
use App\Entity\Post;
use OpenApi\Attributes as OA;

#[OA\Schema(description: 'Post', required: ['id', 'titre', 'dateCreation', 'vues', 'epingle', 'verrouille', 'messages'])]
class PostResponseDTO
{
    #[OA\Property(type: 'integer', example: 1)]
    public int $id;
    #[OA\Property(type: 'string', example: 'Titre du post')]
    public string $titre;
    #[OA\Property(type: 'string', format: 'date-time', example: '2025-07-27 10:00:00')]
    public ?string $dateCreation;
    #[OA\Property(type: 'integer', example: 42)]
    public ?int $vues;
    #[OA\Property(type: 'boolean', example: false)]
    public bool $epingle;
    #[OA\Property(type: 'boolean', example: false)]
    public bool $verrouille;
    /**
     * @var MessageResponseDTO[]
     */
    #[OA\Property(type: 'array', items: new OA\Items(ref: MessageResponseDTO::class))]
    public array $messages;

    public function __construct(
        int $id,
        string $titre,
        ?string $dateCreation,
        ?int $vues,
        bool $epingle,
        bool $verrouille,
        array $messages,
    ) {
        $this->id = $id;
        $this->titre = $titre;
        $this->dateCreation = $dateCreation;
        $this->vues = $vues;
        $this->epingle = $epingle;
        $this->verrouille = $verrouille;
        $this->messages = $messages;
    }

    public static function fromEntity(Post $post): self
    {
        return new self(
            $post->getId(),
            $post->getTitre(),
            $post->getDateCreation()?->format('Y-m-d H:i:s'),
            $post->getVues(),
            $post->isEpingle(),
            $post->isVerrouille(),
            array_map(fn (Message $message) => MessageResponseDTO::fromEntity($message), $post->getMessages()->toArray()),
        );
    }
}
