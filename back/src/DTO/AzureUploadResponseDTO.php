<?php

namespace App\DTO;

/**
 * DTO pour la réponse de génération d'URL d'upload Azure Blob.
 */
final readonly class AzureUploadResponseDTO
{
    public function __construct(
        public readonly string $uploadUrl,
        public readonly string $blobName,
        public readonly string $expiresAt,
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
