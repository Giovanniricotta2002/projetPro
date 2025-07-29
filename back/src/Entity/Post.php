<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\{ArrayCollection, Collection};
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $titre = null;

    #[ORM\Column]
    private ?\DateTime $dateCreation = null;

    #[ORM\Column]
    private ?int $vues = null;

    #[ORM\Column(nullable: true)]
    private ?bool $verrouille = null;

    #[ORM\Column(nullable: true)]
    private ?bool $epingle = null;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'post')]
    private Collection $messages;

    #[ORM\ManyToOne(inversedBy: 'post')]
    private ?Forum $forum = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->verrouille = false;
        $this->messages = new ArrayCollection();
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

    public function getVues(): ?int
    {
        return $this->vues;
    }

    public function setVues(int $vues): static
    {
        $this->vues = $vues;

        return $this;
    }

    public function isVerrouille(): ?bool
    {
        return $this->verrouille;
    }

    public function setVerrouille(?bool $verrouille): static
    {
        $this->verrouille = $verrouille;

        return $this;
    }

    public function isEpingle(): ?bool
    {
        return $this->epingle;
    }

    public function setEpingle(?bool $epingle): static
    {
        $this->epingle = $epingle;

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setPost($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getPost() === $this) {
                $message->setPost(null);
            }
        }

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
}
