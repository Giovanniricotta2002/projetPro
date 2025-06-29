<?php

namespace App\Controller;

use App\DTO\AzureUploadResponseDTO;
use App\Service\AzureBlobImageService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur pour la gestion des images via Azure Blob Storage.
 * Le front-end uploade directement vers Azure via des URLs présignées.
 */
#[Route('/api/images', name: 'api_images_')]
class ImageController extends AbstractController
{
    public function __construct(
        private readonly AzureBlobImageService $blobService,
    ) {
    }

    /**
     * Génère une URL présignée pour que le front puisse uploader directement vers Azure.
     */
    #[Route('/upload-url', name: 'upload_url', methods: ['POST'])]
    #[OA\Post(
        path: '/api/images/upload-url',
        operationId: 'generateImageUploadUrl',
        summary: 'Générer une URL d\'upload Azure',
        description: 'Génère une URL présignée pour uploader directement une image vers Azure Blob Storage',
        tags: ['Images']
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Informations de fichier à uploader',
        content: new OA\JsonContent(
            type: 'object',
            required: ['filename'],
            properties: [
                new OA\Property(property: 'filename', type: 'string', description: 'Nom du fichier avec extension', example: 'photo.jpg'),
                new OA\Property(property: 'expiry', type: 'integer', description: 'Durée de validité en secondes', example: 3600)
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'URL d\'upload générée avec succès',
        content: new OA\JsonContent(ref: new Model(type: AzureUploadResponseDTO::class))
    )]
    #[OA\Response(
        response: 400,
        description: 'Paramètres invalides ou extension non autorisée'
    )]
    public function generateUploadUrl(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['filename']) || empty($data['filename'])) {
            return $this->json(['error' => 'Filename is required'], Response::HTTP_BAD_REQUEST);
        }

        $filename = $data['filename'];
        $expiry = $data['expiry'] ?? 3600; // 1 heure par défaut

        // Validation de l'extension
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions)) {
            return $this->json([
                'error' => 'Invalid file extension',
                'allowed' => $allowedExtensions,
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $uploadData = $this->blobService->generateUploadUrl($filename, $expiry);

            return $this->json([
                'success' => true,
                'data' => $uploadData,
                'instructions' => [
                    'method' => 'PUT',
                    'headers' => $uploadData['headers'],
                    'note' => 'Envoyez le fichier en tant que body de la requête PUT',
                ],
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Failed to generate upload URL',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Vérifie qu'un upload a bien eu lieu et retourne l'URL publique.
     */
    #[Route('/verify', name: 'verify', methods: ['POST'])]
    public function verifyUpload(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['blob_name']) || empty($data['blob_name'])) {
            return $this->json(['error' => 'blob_name is required'], Response::HTTP_BAD_REQUEST);
        }

        $blobName = $data['blob_name'];

        if ($this->blobService->verifyUpload($blobName)) {
            return $this->json([
                'success' => true,
                'image_url' => $this->blobService->getImageUrl($blobName),
                'blob_name' => $blobName,
            ]);
        }

        return $this->json([
            'success' => false,
            'error' => 'Image not found or upload failed',
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * Génère une URL présignée pour accéder à une image privée.
     */
    #[Route('/signed-url/{blobName}', name: 'signed_url', methods: ['GET'])]
    public function getSignedUrl(string $blobName, Request $request): JsonResponse
    {
        $expiry = (int) $request->query->get('expiry', 3600);

        try {
            $signedUrl = $this->blobService->getSignedImageUrl($blobName, $expiry);

            return $this->json([
                'signed_url' => $signedUrl,
                'expires_at' => gmdate('Y-m-d\TH:i:s\Z', time() + $expiry),
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Failed to generate signed URL',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Supprime une image d'Azure Blob Storage.
     */
    #[Route('/{blobName}', name: 'delete', methods: ['DELETE'])]
    public function deleteImage(string $blobName): JsonResponse
    {
        // Décoder le nom du blob (il peut contenir des slashes)
        $blobName = urldecode($blobName);

        try {
            if ($this->blobService->deleteImage($blobName)) {
                return $this->json([
                    'success' => true,
                    'message' => 'Image deleted successfully',
                ]);
            }

            return $this->json([
                'success' => false,
                'error' => 'Failed to delete image',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Failed to delete image',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Retourne l'URL publique d'une image.
     */
    #[Route('/url/{blobName}', name: 'public_url', methods: ['GET'])]
    public function getPublicUrl(string $blobName): JsonResponse
    {
        $blobName = urldecode($blobName);

        return $this->json([
            'image_url' => $this->blobService->getImageUrl($blobName),
            'blob_name' => $blobName,
        ]);
    }
}
