<?php

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(description: 'Machine creation request', required: ['nom', 'image', 'infoMachines'])]
class MachineCreateRequestDTO
{
    #[OA\Property(type: 'string', example: 'PC Portable')]
    public string $nom;
    #[OA\Property(type: 'string', example: 'image.png')]
    public string $image;
    #[OA\Property(type: 'array', items: new OA\Items(ref: InfoMachineBaseDTO::class))]
    public array $infoMachines;
}
