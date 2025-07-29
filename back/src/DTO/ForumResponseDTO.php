<?php

namespace App\DTO;

use App\Entity\Forum;
use OpenApi\Attributes as OA;

#[OA\Schema(description: 'Forum', required: ['id', 'titre', 'dateCreation', 'description', 'ordreAffichage', 'visible', 'slug', 'createdAt'])]
class ForumResponseDTO
{
    #[OA\Property(type: 'integer', example: 1)]
    public int $id;
    #[OA\Property(type: 'string', example: 'Forum général')]
    public string $titre;
    #[OA\Property(type: 'string', format: 'date-time', example: '2025-07-27T10:00:00')]
    public string $dateCreation;
    #[OA\Property(type: 'string', example: 'Description du forum')]
    public string $description;
    #[OA\Property(type: 'integer', example: 1)]
    public int $ordreAffichage;
    #[OA\Property(type: 'boolean', example: true)]
    public bool $visible;
    #[OA\Property(type: 'string', example: 'forum-general')]
    public string $slug;
    #[OA\Property(type: 'string', format: 'date-time', example: '2025-07-27T10:00:00')]
    public string $createdAt;

    public function __construct(int $id, string $titre, ?string $dateCreation, ?string $description, int $ordreAffichage, bool $visible, ?string $slug, ?string $createdAt)
    {
        $this->id = $id;
        $this->titre = $titre;
        $this->dateCreation = $dateCreation;
        $this->description = $description;
        $this->ordreAffichage = $ordreAffichage;
        $this->visible = $visible;
        $this->slug = $slug;
        $this->createdAt = $createdAt;
    }

    public static function fromEntity(Forum $forum): self
    {
        return new self(
            $forum->getId(),
            $forum->getTitre(),
            $forum->getDateCreation()?->format('Y-m-d H:i:s'),
            $forum->getDescription(),
            $forum->getOrdreAffichage(),
            $forum->isVisible(),
            $forum->getSlug(),
            $forum->getCreatedAt()?->format('Y-m-d H:i:s'),
        );
    }

    /**
     * Summary of toArray.
     *
     * @return array{createdAt: string, dateCreation: string, description: string, id: int, ordreAffichage: int, slug: string, titre: string, visible: bool}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'titre' => $this->titre,
            'dateCreation' => $this->dateCreation,
            'description' => $this->description,
            'ordreAffichage' => $this->ordreAffichage,
            'visible' => $this->visible,
            'slug' => $this->slug,
            'createdAt' => $this->createdAt,
        ];
    }
}
