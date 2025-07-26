<?php

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(description: 'InfoMachine update', allOf: [
    new OA\Schema(ref: InfoMachineBaseDTO::class)
], required: ['id', 'remove'])]
class InfoMachineUpdateDTO extends InfoMachineBaseDTO
{
    #[OA\Property(type: 'integer', example: 1)]
    public int $id;
    #[OA\Property(type: 'boolean', example: false)]
    public bool $remove;
}
