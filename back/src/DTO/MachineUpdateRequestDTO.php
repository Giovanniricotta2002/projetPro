<?php

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(description: 'Machine update request', required: ['nom', 'image', 'description', 'infoMachines'])]
class MachineUpdateRequestDTO
{
    #[OA\Property(type: 'string', example: 'PC Portable')]
    public string $nom;
    #[OA\Property(type: 'string', example: 'image.png')]
    public string $image;
    #[OA\Property(type: 'string', example: 'Ordinateur performant')]
    public string $description;
    #[OA\Property(type: 'array', items: new OA\Items(ref: InfoMachineUpdateDTO::class))]
    public array $infoMachines;
}
