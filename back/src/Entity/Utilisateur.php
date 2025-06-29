<?php

namespace App\Entity;

use App\Enum\UserStatus;
use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateCreation = null;

    #[ORM\Column]
    private ?bool $anonimus = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $lastVisit = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $mail = null;

    #[ORM\Column(type: 'string', enumType: UserStatus::class)]
    private UserStatus $status = UserStatus::PENDING;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $updatedAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $deletedAt = null;

    /**
     * @var Collection<int, Droit>
     */
    #[ORM\ManyToMany(targetEntity: Droit::class, mappedBy: 'idUtilisateur')]
    private Collection $droits;

    #[ORM\OneToOne(mappedBy: 'utilisateur', cascade: ['persist', 'remove'])]
    private ?Message $message = null;

    /**
     * @var Collection<int, Forum>
     */
    #[ORM\OneToMany(targetEntity: Forum::class, mappedBy: 'utilisateur')]
    private Collection $forums;

    #[ORM\OneToOne(mappedBy: 'moderateur', cascade: ['persist', 'remove'])]
    private ?Moderations $moderations = null;

    #[ORM\OneToOne(mappedBy: 'cible', cascade: ['persist', 'remove'])]
    private ?Moderations $cible = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->createdAt = new \DateTimeImmutable();
        $this->anonimus = false;
        $this->status = UserStatus::PENDING;

        $this->droits = new ArrayCollection();
        $this->forums = new ArrayCollection();
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getUserIdentifier(): string
    {
        return ''; 
    }

    public function eraseCredentials(): void
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

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

    public function isAnonimus(): ?bool
    {
        return $this->anonimus;
    }

    public function setAnonimus(bool $anonimus): static
    {
        $this->anonimus = $anonimus;

        return $this;
    }

    public function getLastVisit(): ?\DateTime
    {
        return $this->lastVisit;
    }

    public function setLastVisit(?\DateTime $lastVisit): static
    {
        $this->lastVisit = $lastVisit;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(?string $mail): static
    {
        $this->mail = $mail;

        return $this;
    }

    public function getStatus(): UserStatus
    {
        return $this->status;
    }

    public function setStatus(UserStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Vérifie si l'utilisateur peut se connecter selon son statut.
     */
    public function canLogin(): bool
    {
        return $this->status->canLogin();
    }

    /**
     * Vérifie si l'utilisateur a accès à l'application.
     */
    public function isAccessible(): bool
    {
        return $this->status->isAccessible();
    }

    /**
     * Vérifie si le compte nécessite une action de l'utilisateur.
     */
    public function requiresUserAction(): bool
    {
        return $this->status->requiresUserAction();
    }

    /**
     * Active le compte utilisateur.
     */
    public function activate(): static
    {
        $this->status = UserStatus::ACTIVE;
        return $this;
    }

    /**
     * Désactive le compte utilisateur.
     */
    public function deactivate(): static
    {
        $this->status = UserStatus::INACTIVE;
        return $this;
    }

    /**
     * Suspend le compte utilisateur.
     */
    public function suspend(): static
    {
        $this->status = UserStatus::SUSPENDED;
        return $this;
    }

    /**
     * Bannit le compte utilisateur.
     */
    public function ban(): static
    {
        $this->status = UserStatus::BANNED;
        return $this;
    }

    /**
     * Marque le compte comme supprimé (soft delete).
     */
    public function markAsDeleted(): static
    {
        $this->status = UserStatus::DELETED;
        $this->deletedAt = new \DateTime();
        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDeletedAt(): ?\DateTime
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(\DateTime $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @return Collection<int, Droit>
     */
    public function getDroits(): Collection
    {
        return $this->droits;
    }

    public function addDroit(Droit $droit): static
    {
        if (!$this->droits->contains($droit)) {
            $this->droits->add($droit);
            $droit->addIdUtilisateur($this);
        }

        return $this;
    }

    public function removeDroit(Droit $droit): static
    {
        if ($this->droits->removeElement($droit)) {
            $droit->removeIdUtilisateur($this);
        }

        return $this;
    }

    public function getMessage(): ?Message
    {
        return $this->message;
    }

    public function setMessage(?Message $message): static
    {
        // unset the owning side of the relation if necessary
        if ($message === null && $this->message !== null) {
            $this->message->setUtilisateur(null);
        }

        // set the owning side of the relation if necessary
        if ($message !== null && $message->getUtilisateur() !== $this) {
            $message->setUtilisateur($this);
        }

        $this->message = $message;

        return $this;
    }

    /**
     * @return Collection<int, Forum>
     */
    public function getForums(): Collection
    {
        return $this->forums;
    }

    public function addForum(Forum $forum): static
    {
        if (!$this->forums->contains($forum)) {
            $this->forums->add($forum);
            $forum->setUtilisateur($this);
        }

        return $this;
    }

    public function removeForum(Forum $forum): static
    {
        if ($this->forums->removeElement($forum)) {
            // set the owning side to null (unless already changed)
            if ($forum->getUtilisateur() === $this) {
                $forum->setUtilisateur(null);
            }
        }

        return $this;
    }

    public function getModerations(): ?Moderations
    {
        return $this->moderations;
    }

    public function setModerations(?Moderations $moderations): static
    {
        // unset the owning side of the relation if necessary
        if ($moderations === null && $this->moderations !== null) {
            $this->moderations->setModerateur(null);
        }

        // set the owning side of the relation if necessary
        if ($moderations !== null && $moderations->getModerateur() !== $this) {
            $moderations->setModerateur($this);
        }

        $this->moderations = $moderations;

        return $this;
    }

    public function getCible(): ?Moderations
    {
        return $this->cible;
    }

    public function setCible(?Moderations $cible): static
    {
        // unset the owning side of the relation if necessary
        if ($cible === null && $this->cible !== null) {
            $this->cible->setCible(null);
        }

        // set the owning side of the relation if necessary
        if ($cible !== null && $cible->getCible() !== $this) {
            $cible->setCible($this);
        }

        $this->cible = $cible;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
    
}
