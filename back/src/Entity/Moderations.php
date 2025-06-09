<?php

namespace App\Entity;

use App\Repository\ModerationsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ModerationsRepository::class)]
class Moderations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'moderations', cascade: ['persist', 'remove'])]
    private ?Utilisateur $moderateur = null;

    #[ORM\Column(length: 30)]
    private ?string $typeAction = null;

    #[ORM\OneToOne(inversedBy: 'cible', cascade: ['persist', 'remove'])]
    private ?Utilisateur $cible = null;

    #[ORM\Column(length: 100)]
    private ?string $raison = null;

    #[ORM\Column]
    private ?\DateTime $dateAction = null;

    public function getId(): ?int
    {
        $this->dateAction = new \DateTime();

        return $this->id;
    }

    public function getModerateur(): ?Utilisateur
    {
        return $this->moderateur;
    }

    public function setModerateur(?Utilisateur $moderateur): static
    {
        $this->moderateur = $moderateur;

        return $this;
    }

    public function getTypeAction(): ?string
    {
        return $this->typeAction;
    }

    public function setTypeAction(string $typeAction): static
    {
        $this->typeAction = $typeAction;

        return $this;
    }

    public function getCible(): ?Utilisateur
    {
        return $this->cible;
    }

    public function setCible(?Utilisateur $cible): static
    {
        $this->cible = $cible;

        return $this;
    }

    public function getRaison(): ?string
    {
        return $this->raison;
    }

    public function setRaison(string $raison): static
    {
        $this->raison = $raison;

        return $this;
    }

    public function getDateAction(): ?\DateTime
    {
        return $this->dateAction;
    }

    public function setDateAction(\DateTime $dateAction): static
    {
        $this->dateAction = $dateAction;

        return $this;
    }
}
