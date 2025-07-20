<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class MachineCreateDto
{
    #[Assert\NotBlank]
    public string $nom;

    #[Assert\NotBlank]
    public string $image;

    #[Assert\NotBlank]
    public string $infoMachines;
}
