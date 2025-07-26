<?php

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(description: 'Forum creation request', required: ['titre', 'categories', 'description', 'ordreAffichage', 'visible', 'utilisateur'])]
class ForumCreateRequestDTO
{
    #[OA\Property(type: 'string', example: 'Forum général')]
    public string $titre;
    #[OA\Property(type: 'integer', example: 1)]
    public int $categories;
    #[OA\Property(type: 'string', example: 'Description du forum')]
    public string $description;
    #[OA\Property(type: 'integer', example: 1)]
    public int $ordreAffichage;
    #[OA\Property(type: 'boolean', example: true)]
    public bool $visible;
    #[OA\Property(type: 'integer', example: 1)]
    public int $utilisateur;
}
