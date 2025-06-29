<?php

namespace App\Entity;

use App\Repository\ForumRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ForumRepository::class)]
class Forum
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $titre = null;

    #[ORM\Column]
    private ?\DateTime $dateCreation = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dateCloture = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $ordreAffichage = null;

    #[ORM\Column]
    private ?bool $visible = null;

    #[ORM\Column(length: 50)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $updatedAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $deletedAt = null;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'forum')]
    private Collection $post;

    /**
     * @var Collection<int, CategorieForum>
     */
    #[ORM\OneToMany(targetEntity: CategorieForum::class, mappedBy: 'forum')]
    private Collection $categorieForums;

    #[ORM\ManyToOne(inversedBy: 'forums')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\OneToOne(mappedBy: 'forum', cascade: ['persist', 'remove'])]
    private ?Machine $machine = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();

        $this->post = new ArrayCollection();
        $this->categorieForums = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

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

    public function getDateCloture(): ?\DateTime
    {
        return $this->dateCloture;
    }

    public function setDateCloture(?\DateTime $dateCloture): static
    {
        $this->dateCloture = $dateCloture;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getOrdreAffichage(): ?int
    {
        return $this->ordreAffichage;
    }

    public function setOrdreAffichage(int $ordreAffichage): static
    {
        $this->ordreAffichage = $ordreAffichage;

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

    /**
     * @return Collection<int, Post>
     */
    public function getPost(): Collection
    {
        return $this->post;
    }

    public function addPost(Post $post): static
    {
        if (!$this->post->contains($post)) {
            $this->post->add($post);
            $post->setForum($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, CategorieForum>
     */
    public function getCategorieForums(): Collection
    {
        return $this->categorieForums;
    }

    public function addCategorieForum(CategorieForum $categorieForum): static
    {
        if (!$this->categorieForums->contains($categorieForum)) {
            $this->categorieForums->add($categorieForum);
            $categorieForum->setForum($this);
        }

        return $this;
    }

    public function removeCategorieForum(CategorieForum $categorieForum): static
    {
        if ($this->categorieForums->removeElement($categorieForum)) {
            // set the owning side to null (unless already changed)
            if ($categorieForum->getForum() === $this) {
                $categorieForum->setForum(null);
            }
        }

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getMachine(): ?Machine
    {
        return $this->machine;
    }

    public function setMachine(?Machine $machine): static
    {
        // unset the owning side of the relation if necessary
        if ($machine === null && $this->machine !== null) {
            $this->machine->setForum(null);
        }

        // set the owning side of the relation if necessary
        if ($machine !== null && $machine->getForum() !== $this) {
            $machine->setForum($this);
        }

        $this->machine = $machine;

        return $this;
    }
}
