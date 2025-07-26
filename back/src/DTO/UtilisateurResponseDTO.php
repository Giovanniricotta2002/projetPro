<?php

namespace App\DTO;

use App\Entity\Utilisateur;
use OpenApi\Attributes as OA;

#[OA\Schema(description: 'Utilisateur', required: ['id', 'username', 'roles', 'dateCreation', 'anonimus', 'status', 'mail', 'lastVisit', 'createdAt'])]
class UtilisateurResponseDTO
{
    #[OA\Property(type: 'integer', example: 1)]
    public int $id;
    #[OA\Property(type: 'string', example: 'johndoe')]
    public string $username;
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string'), example: '["ROLE_USER"]')]
    public array $roles;
    #[OA\Property(type: 'string', format: 'date', example: '2024-01-01')]
    public string $dateCreation;
    #[OA\Property(type: 'boolean', example: false)]
    public bool $anonimus;
    #[OA\Property(type: 'string', example: 'active')]
    public string $status;
    #[OA\Property(type: 'string', example: 'johndoe@example.com')]
    public string $mail;
    #[OA\Property(type: 'string', format: 'date', example: '2025-07-19')]
    public ?string $lastVisit;
    #[OA\Property(type: 'string', format: 'date-time', example: '2025-07-27T10:00:00')]
    public string $createdAt;

    public function __construct(
        int $id,
        string $username,
        array $roles,
        string $dateCreation,
        bool $anonimus,
        string $status,
        string $mail,
        ?string $lastVisit,
        string $createdAt,
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->roles = $roles;
        $this->dateCreation = $dateCreation;
        $this->anonimus = $anonimus;
        $this->status = $status;
        $this->mail = $mail;
        $this->lastVisit = $lastVisit;
        $this->createdAt = $createdAt;
    }

    public static function fromEntity(Utilisateur $user): self
    {
        return new self(
            $user->getId(),
            $user->getUsername(),
            $user->getRoles(),
            $user->getDateCreation()?->format('Y-m-d'),
            $user->isAnonimus(),
            $user->getStatus()->value,
            $user->getMail(),
            $user->getLastVisit()?->format('Y-m-d'),
            $user->getCreatedAt()?->format('Y-m-d H:i:s'),
        );
    }
}
