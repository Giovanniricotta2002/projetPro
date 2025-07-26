<?php

namespace App\DTO;

use App\Entity\Utilisateur;
use OpenApi\Attributes as OA;

#[OA\Schema(description: 'Utilisateur du message', required: ['id', 'username', 'anonimus'])]
class MessageUserResponseDTO
{
    #[OA\Property(type: 'integer', example: 1)]
    public int $id;
    #[OA\Property(type: 'string', example: 'john_doe')]
    public string $username;
    #[OA\Property(type: 'boolean', example: true)]
    public bool $anonimus;

    public function __construct(int $id, string $username, bool $anonimus)
    {
        $this->id = $id;
        $this->username = $username;
        $this->anonimus = $anonimus;
    }

    public static function fromEntity(Utilisateur $user): self
    {
        return new self(
            $user->getId(),
            $user->getUsername(),
            $user->isAnonimus(),
        );
    }
}
