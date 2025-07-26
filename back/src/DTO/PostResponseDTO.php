<?php

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(description: 'Post', required: ['id', 'titre', 'dateCreation', 'description', 'ordreAffichage', 'visible', 'slug', 'createdAt'])]
class PostResponseDTO
{
    #[OA\Property(type: 'integer', example: 1)]
    public int $id;
    #[OA\Property(type: 'string', example: 'Titre du post')]
    public string $titre;
    #[OA\Property(type: 'string', format: 'date-time', example: '2025-07-27T10:00:00')]
    public string $dateCreation;
    #[OA\Property(type: 'string', example: 'Description du post')]
    public string $description;
    #[OA\Property(type: 'integer', example: 1)]
    public int $ordreAffichage;
    #[OA\Property(type: 'boolean', example: true)]
    public bool $visible;
    #[OA\Property(type: 'string', example: 'post-slug')]
    public string $slug;
    #[OA\Property(type: 'string', format: 'date-time', example: '2025-07-27T10:00:00')]
    public string $createdAt;

    public function __construct($id, $titre, $dateCreation, $description, $ordreAffichage, $visible, $slug, $createdAt)
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

    public static function fromEntity($post): self
    {
        return new self(
            $post->getId(),
            $post->getTitre(),
            $post->getDateCreation()?->format('Y-m-d H:i:s'),
            $post->getDescription(),
            $post->getOrdreAffichage(),
            $post->isVisible(),
            $post->getSlug(),
            $post->getCreatedAt()?->format('Y-m-d H:i:s'),
        );
    }
}
