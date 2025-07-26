<?php

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(description: 'InfoMachine base', required: ['text', 'type'])]
class InfoMachineBaseDTO
{
    #[OA\Property(type: 'string', example: 'RAM 16Go')]
    public string $text;
    #[OA\Property(type: 'string', example: 'hardware')]
    public string $type;
}
