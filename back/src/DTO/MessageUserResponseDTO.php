<?php

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(description: 'Utilisateur du message', required: ['id', 'username', 'email'])]
class MessageUserResponseDTO
{
    #[OA\Property(type: 'integer', example: 1)]
    public int $id;
    #[OA\Property(type: 'string', example: 'john_doe')]
    public string $username;
    #[OA\Property(type: 'string', example: 'john@example.com')]
    public string $email;

    public function __construct($id, $username, $email)
    {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
    }

    public static function fromEntity($user): self
    {
        return new self(
            $user->getId(),
            $user->getUsername(),
            $user->getEmail(),
        );
    }
}
