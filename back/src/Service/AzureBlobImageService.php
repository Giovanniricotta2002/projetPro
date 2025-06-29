<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Service pour gérer les uploads directs vers Azure Blob Storage depuis le front-end.
 * Le backend génère des URLs présignées et gère les métadonnées des images.
 */
class AzureBlobImageService
{
    public function __construct(
        #[Autowire('%env(AZURE_STORAGE_ACCOUNT)%')]
        private readonly string $storageAccount,
        #[Autowire('%env(AZURE_STORAGE_KEY)%')]
        private readonly string $storageKey,
        #[Autowire('%env(AZURE_STORAGE_CONTAINER)%')]
        private readonly string $containerName,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Génère une URL présignée pour permettre au front d'uploader directement.
     *
     * @param string $fileName Nom du fichier à uploader
     * @param int    $expiry   Durée de validité en secondes (défaut: 1 heure)
     *
     * @return array URLs et paramètres pour l'upload front-end
     */
    public function generateUploadUrl(string $fileName, int $expiry = 3600): array
    {
        $blobName = $this->generateUniqueBlobName($fileName);
        $expiryTime = gmdate('Y-m-d\TH:i:s\Z', time() + $expiry);

        // Génération de la signature SAS (Shared Access Signature)
        $sasToken = $this->generateSasToken($blobName, $expiryTime);

        $uploadUrl = sprintf(
            'https://%s.blob.core.windows.net/%s/%s?%s',
            $this->storageAccount,
            $this->containerName,
            $blobName,
            $sasToken
        );

        $this->logger->info('Generated Azure Blob upload URL', [
            'blob_name' => $blobName,
            'expires_at' => $expiryTime,
            'original_filename' => $fileName,
        ]);

        return [
            'upload_url' => $uploadUrl,
            'blob_name' => $blobName,
            'expires_at' => $expiryTime,
            'headers' => [
                'x-ms-blob-type' => 'BlockBlob',
                'x-ms-blob-content-type' => $this->getMimeType($fileName),
            ],
        ];
    }

    /**
     * Génère l'URL publique d'accès à une image.
     *
     * @param string $blobName Nom du blob dans Azure
     *
     * @return string URL publique de l'image
     */
    public function getImageUrl(string $blobName): string
    {
        return sprintf(
            'https://%s.blob.core.windows.net/%s/%s',
            $this->storageAccount,
            $this->containerName,
            $blobName
        );
    }

    /**
     * Génère une URL présignée pour la lecture d'une image privée.
     *
     * @param string $blobName Nom du blob
     * @param int    $expiry   Durée de validité en secondes
     *
     * @return string URL présignée pour accès temporaire
     */
    public function getSignedImageUrl(string $blobName, int $expiry = 3600): string
    {
        $expiryTime = gmdate('Y-m-d\TH:i:s\Z', time() + $expiry);
        $sasToken = $this->generateSasToken($blobName, $expiryTime, 'r'); // read only

        return sprintf(
            'https://%s.blob.core.windows.net/%s/%s?%s',
            $this->storageAccount,
            $this->containerName,
            $blobName,
            $sasToken
        );
    }

    /**
     * Valide si un upload a bien eu lieu côté Azure.
     *
     * @param string $blobName Nom du blob à vérifier
     *
     * @return bool True si le blob existe
     */
    public function verifyUpload(string $blobName): bool
    {
        try {
            // Simple vérification via HEAD request
            $url = $this->getImageUrl($blobName);
            $headers = get_headers($url);

            $exists = $headers && strpos($headers[0], '200') !== false;

            if ($exists) {
                $this->logger->info('Upload verified successfully', ['blob_name' => $blobName]);
            } else {
                $this->logger->warning('Upload verification failed', ['blob_name' => $blobName]);
            }

            return $exists;
        } catch (\Exception $e) {
            $this->logger->error('Error verifying upload', [
                'blob_name' => $blobName,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Génère un nom unique pour le blob en conservant l'extension.
     */
    private function generateUniqueBlobName(string $originalFileName): string
    {
        $pathInfo = pathinfo($originalFileName);
        $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';

        $timestamp = date('Y/m/d'); // Organisation par date
        $uniqueId = uniqid('img_', true);

        return sprintf('%s/%s%s', $timestamp, $uniqueId, $extension);
    }

    /**
     * Génère un token SAS pour Azure Blob Storage.
     */
    private function generateSasToken(string $blobName, string $expiry, string $permissions = 'w'): string
    {
        $startTime = gmdate('Y-m-d\TH:i:s\Z', time() - 300); // 5 min avant pour éviter les problèmes d'horloge

        // Construction de la chaîne à signer
        $stringToSign = implode("\n", [
            $permissions,           // Permissions
            $startTime,            // Start time
            $expiry,               // Expiry time
            '/blob/' . $this->storageAccount . '/' . $this->containerName . '/' . $blobName, // Resource
            '',                    // Identifier (vide pour ad-hoc)
            '2020-04-08',         // API version
            'https',              // Protocol
            '',                   // IP range (vide)
            '',                   // Reserved field
            '',                   // Reserved field
            '',                   // Reserved field
        ]);

        // Signature avec la clé de stockage
        $signature = base64_encode(hash_hmac('sha256', $stringToSign, base64_decode($this->storageKey), true));

        // Construction du token SAS
        return http_build_query([
            'sv' => '2020-04-08',    // API version
            'sr' => 'b',             // Resource type (blob)
            'sp' => $permissions,     // Permissions
            'st' => $startTime,      // Start time
            'se' => $expiry,         // Expiry time
            'spr' => 'https',        // Protocol
            'sig' => $signature,      // Signature
        ]);
    }

    /**
     * Détermine le type MIME basé sur l'extension du fichier.
     */
    private function getMimeType(string $fileName): string
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            default => 'image/jpeg',
        };
    }

    /**
     * Nettoie un nom de blob (supprime de Azure).
     *
     * @param string $blobName Nom du blob à supprimer
     *
     * @return bool True si supprimé avec succès
     */
    public function deleteImage(string $blobName): bool
    {
        try {
            $expiryTime = gmdate('Y-m-d\TH:i:s\Z', time() + 300); // 5 min
            $sasToken = $this->generateSasToken($blobName, $expiryTime, 'd'); // delete permission

            $deleteUrl = sprintf(
                'https://%s.blob.core.windows.net/%s/%s?%s',
                $this->storageAccount,
                $this->containerName,
                $blobName,
                $sasToken
            );

            // Utilisation de cURL pour envoyer DELETE request
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $deleteUrl,
                CURLOPT_CUSTOMREQUEST => 'DELETE',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => false,
                CURLOPT_HTTPHEADER => ['x-ms-version: 2020-04-08'],
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $success = $httpCode === 202; // Accepted

            if ($success) {
                $this->logger->info('Image deleted successfully', ['blob_name' => $blobName]);
            } else {
                $this->logger->warning('Failed to delete image', [
                    'blob_name' => $blobName,
                    'http_code' => $httpCode,
                ]);
            }

            return $success;
        } catch (\Exception $e) {
            $this->logger->error('Error deleting image', [
                'blob_name' => $blobName,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
