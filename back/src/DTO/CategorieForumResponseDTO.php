<?php

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(
    description: 'Catégorie de forum',
    required: ['id', 'name', 'ordre']
)]
class CategorieForumResponseDTO
{
    #[OA\Property(description: 'ID de la catégorie', type: 'integer', example: 1)]
    public int $id;

    #[OA\Property(description: 'Nom de la catégorie', type: 'string', example: 'Général')]
    public string $name;

    #[OA\Property(description: 'Ordre d\'affichage', type: 'integer', example: 1)]
    public int $ordre;

    public function __construct(int $id, string $name, int $ordre)
    {
        $this->id = $id;
        $this->name = $name;
        $this->ordre = $ordre;
    }

    public static function fromEntity($entity): self
    {
        return new self($entity->getId(), $entity->getName(), $entity->getOrdre());
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'ordre' => $this->ordre,
        ];
    }
}
