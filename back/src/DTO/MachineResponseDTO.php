<?php

namespace App\DTO;

use App\Entity\Machine;
use OpenApi\Attributes as OA;

#[OA\Schema(description: 'Machine', required: ['id', 'name', 'visible', 'image', 'description', 'forum', 'infoMachines'])]
class MachineResponseDTO
{
    #[OA\Property(type: 'integer', example: 1)]
    public int $id;
    #[OA\Property(type: 'string', example: 'PC Portable')]
    public string $name;
    #[OA\Property(type: 'boolean', example: true)]
    public bool $visible;
    #[OA\Property(type: 'string', example: 'image.png')]
    public string $image;
    #[OA\Property(type: 'string', example: 'Ordinateur performant')]
    public string $description;
    #[OA\Property(type: 'object', properties: [new OA\Property(property: 'id', type: 'integer', example: 1)])]
    public $forum;
    #[OA\Property(type: 'array', items: new OA\Items(type: 'object', properties: [new OA\Property(property: 'id', type: 'integer', example: 1), new OA\Property(property: 'text', type: 'string', example: 'RAM 16Go'), new OA\Property(property: 'type', type: 'string', example: 'hardware')]))]
    public array $infoMachines;

    public function __construct($id, $name, $visible, $image, $description, $forum, $infoMachines)
    {
        $this->id = $id;
        $this->name = $name;
        $this->visible = $visible;
        $this->image = $image;
        $this->description = $description;
        $this->forum = $forum;
        $this->infoMachines = $infoMachines;
    }

    public static function fromEntity(Machine $machine): self
    {
        return new self(
            $machine->getId(),
            $machine->getName(),
            $machine->isVisible(),
            $machine->getImage(),
            $machine->getDescription(),
            $machine->getForum() ? ['id' => $machine->getForum()->getId()] : null,
            array_map(function ($im) {
                return [
                    'id' => $im->getId(),
                    'text' => $im->getText(),
                    'type' => $im->getType(),
                ];
            }, $machine->getInfoMachines()->toArray())
        );
    }
}
