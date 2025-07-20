<?php

namespace App\Entity;

use App\Repository\InfoMachineRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InfoMachineRepository::class)]
class InfoMachine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $text = null;

    #[ORM\Column(length: 30)]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'infoMachines', cascade: ['persist'])]
    private ?Machine $machine = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getMachine(): ?Machine
    {
        return $this->machine;
    }

    public function setMachine(?Machine $machine): static
    {
        $this->machine = $machine;

        return $this;
    }
}
