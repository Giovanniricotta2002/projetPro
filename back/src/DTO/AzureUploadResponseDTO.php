<?php

namespace App\DTO;

use OpenApi\Attributes as OA;

/**
 * DTO pour la réponse de génération d'URL d'upload Azure Blob.
 */
#[OA\Schema(
    title: 'Réponse d\'upload Azure',
    description: 'Informations pour uploader directement vers Azure Blob Storage',
    type: 'object',
    required: ['upload_url', 'blob_name', 'expires_at', 'headers']
)]
final readonly class AzureUploadResponseDTO
{
    public function __construct(
        #[OA\Property(
            property: 'upload_url',
            type: 'string',
            description: 'URL présignée pour l\'upload',
            example: 'https://storage.blob.core.windows.net/container/blob?sig=...'
        )]
        public readonly string $uploadUrl,

        #[OA\Property(
            property: 'blob_name',
            type: 'string',
            description: 'Nom du blob dans Azure',
            example: 'images/uuid-photo.jpg'
        )]
        public readonly string $blobName,

        #[OA\Property(
            property: 'expires_at',
            type: 'string',
            format: 'date-time',
            description: 'Date d\'expiration de l\'URL',
            example: '2025-06-29T15:30:00Z'
        )]
        public readonly string $expiresAt,

        #[OA\Property(
            property: 'headers',
            type: 'object',
            description: 'Headers requis pour l\'upload',
            example: ['x-ms-blob-type' => 'BlockBlob', 'Content-Type' => 'image/jpeg']
        )]
        public readonly array $headers,
    ) {
    }

    /**
     * Crée un DTO depuis les données du service Azure.
     */
    public static function fromServiceData(array $data): self
    {
        return new self(
            uploadUrl: $data['upload_url'],
            blobName: $data['blob_name'],
            expiresAt: $data['expires_at'],
            headers: $data['headers']
        );
    }

    /**
     * Convertit le DTO en tableau associatif.
     */
    public function toArray(): array
    {
        return [
            'upload_url' => $this->uploadUrl,
            'blob_name' => $this->blobName,
            'expires_at' => $this->expiresAt,
            'headers' => $this->headers,
        ];
    }

    /**
     * Vérifie si l'URL d'upload est encore valide.
     */
    public function isExpired(): bool
    {
        $expiryTimestamp = strtotime($this->expiresAt);

        return $expiryTimestamp <= time();
    }

    /**
     * Retourne le temps restant avant expiration en secondes.
     */
    public function getTimeUntilExpiry(): int
    {
        $expiryTimestamp = strtotime($this->expiresAt);

        return max(0, $expiryTimestamp - time());
    }
}
