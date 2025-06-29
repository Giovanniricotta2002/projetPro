<?php

namespace App\DTO;

use OpenApi\Attributes as OA;

/**
 * DTO pour la réponse de génération d'URL d'upload Azure Blob.
 */
#[OA\Schema(
    schema: 'AzureUploadResponse',
    title: 'Réponse URL d\'upload Azure',
    description: 'Structure de la réponse pour l\'upload direct vers Azure Blob Storage',
    type: 'object',
    required: ['upload_url', 'blob_name', 'expires_at', 'headers']
)]
final readonly class AzureUploadResponseDTO
{
    public function __construct(
        #[OA\Property(
            property: 'upload_url',
            description: 'URL présignée pour l\'upload direct vers Azure',
            type: 'string',
            example: 'https://account.blob.core.windows.net/images/2025/06/29/img_abc123.jpg?sv=2020-04-08&sr=b&sp=w...'
        )]
        public readonly string $uploadUrl,
        #[OA\Property(
            property: 'blob_name',
            description: 'Nom unique du blob généré (à conserver pour les opérations futures)',
            type: 'string',
            example: '2025/06/29/img_667f8b2c1a8e9f.jpg'
        )]
        public readonly string $blobName,
        #[OA\Property(
            property: 'expires_at',
            description: 'Date et heure d\'expiration de l\'URL (ISO 8601)',
            type: 'string',
            format: 'date-time',
            example: '2025-06-29T15:30:00Z'
        )]
        public readonly string $expiresAt,
        #[OA\Property(
            property: 'headers',
            description: 'Headers HTTP requis pour l\'upload vers Azure',
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'x-ms-blob-type',
                    type: 'string',
                    example: 'BlockBlob'
                ),
                new OA\Property(
                    property: 'x-ms-blob-content-type',
                    type: 'string',
                    example: 'image/jpeg'
                ),
            ]
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
