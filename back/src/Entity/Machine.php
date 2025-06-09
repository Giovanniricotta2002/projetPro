<?php

namespace App\Entity;

use App\Repository\MachineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: MachineRepository::class)]
class Machine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'uuid')]
    private ?Uuid $uuid = null;

    #[ORM\Column(length: 30)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTime $dateCreation = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dateModif = null;

    #[ORM\Column]
    private ?bool $visible = null;

    #[ORM\OneToOne(inversedBy: 'machine', cascade: ['persist', 'remove'])]
    private ?Forum $forum = null;

    /**
     * @var Collection<int, InfoMachine>
     */
    #[ORM\OneToMany(targetEntity: InfoMachine::class, mappedBy: 'machine')]
    private Collection $infoMachines;

    #[ORM\Column(type: Types::BLOB)]
    private $image = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->uuid = Uuid::v7();
        $this->dateCreation = new \DateTime();
        $this->visible = true;

        $this->infoMachines = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function setUuid(Uuid $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTime $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDateModif(): ?\DateTime
    {
        return $this->dateModif;
    }

    public function setDateModif(?\DateTime $dateModif): static
    {
        $this->dateModif = $dateModif;

        return $this;
    }

    public function isVisible(): ?bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): static
    {
        $this->visible = $visible;

        return $this;
    }

    public function getForum(): ?Forum
    {
        return $this->forum;
    }

    public function setForum(?Forum $forum): static
    {
        $this->forum = $forum;

        return $this;
    }

    /**
     * @return Collection<int, InfoMachine>
     */
    public function getInfoMachines(): Collection
    {
        return $this->infoMachines;
    }

    public function addInfoMachine(InfoMachine $infoMachine): static
    {
        if (!$this->infoMachines->contains($infoMachine)) {
            $this->infoMachines->add($infoMachine);
            $infoMachine->setMachine($this);
        }

        return $this;
    }

    public function removeInfoMachine(InfoMachine $infoMachine): static
    {
        if ($this->infoMachines->removeElement($infoMachine)) {
            // set the owning side to null (unless already changed)
            if ($infoMachine->getMachine() === $this) {
                $infoMachine->setMachine(null);
            }
        }

        return $this;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image): static
    {
        $this->image = $image;

        return $this;
    }
}
