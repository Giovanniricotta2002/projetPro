<?php

namespace App\Tests\Controller;

use App\Service\AzureBlobImageService;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ImageControllerTest extends WebTestCase
{
    private MockObject $blobServiceMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->blobServiceMock = $this->createMock(AzureBlobImageService::class);
    }

    public function testGenerateUploadUrlSuccess(): void
    {
        // Arrange
        $client = static::createClient();
        
        $filename = 'test-image.jpg';
        $uploadUrl = 'https://storage.blob.core.windows.net/container/test-image.jpg?sig=signature';
        $blobUrl = 'https://storage.blob.core.windows.net/container/test-image.jpg';

        $this->blobServiceMock
            ->expects($this->once())
            ->method('generateUploadUrl')
            ->with($filename, 3600)
            ->willReturn([
                'upload_url' => $uploadUrl,
                'blob_url' => $blobUrl,
                'filename' => $filename,
                'expires_at' => (new \DateTime('+1 hour'))->format('c')
            ]);

        $client->getContainer()->set(AzureBlobImageService::class, $this->blobServiceMock);

        // Act
        $client->request('POST', '/api/images/upload-url', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'filename' => $filename,
            'expiry' => 3600
        ]));

        // Assert
        $this->assertResponseIsSuccessful();
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('upload_url', $responseData);
        $this->assertArrayHasKey('blob_url', $responseData);
        $this->assertArrayHasKey('filename', $responseData);
        $this->assertArrayHasKey('expires_at', $responseData);
        $this->assertEquals($uploadUrl, $responseData['upload_url']);
        $this->assertEquals($blobUrl, $responseData['blob_url']);
        $this->assertEquals($filename, $responseData['filename']);
    }

    public function testGenerateUploadUrlMissingFilename(): void
    {
        // Arrange
        $client = static::createClient();

        // Act
        $client->request('POST', '/api/images/upload-url', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([]));

        // Assert
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('error', $responseData);
    }

    public function testGenerateUploadUrlWithDefaultExpiry(): void
    {
        // Arrange
        $client = static::createClient();
        
        $filename = 'test-image.png';
        $defaultExpiry = 3600; // 1 heure par défaut

        $this->blobServiceMock
            ->expects($this->once())
            ->method('generateUploadUrl')
            ->with($filename, $defaultExpiry)
            ->willReturn([
                'upload_url' => 'https://storage.blob.core.windows.net/container/test-image.png?sig=signature',
                'blob_url' => 'https://storage.blob.core.windows.net/container/test-image.png',
                'filename' => $filename,
                'expires_at' => (new \DateTime('+1 hour'))->format('c')
            ]);

        $client->getContainer()->set(AzureBlobImageService::class, $this->blobServiceMock);

        // Act
        $client->request('POST', '/api/images/upload-url', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'filename' => $filename
            // pas d'expiry fourni
        ]));

        // Assert
        $this->assertResponseIsSuccessful();
    }

    public function testGenerateUploadUrlServiceException(): void
    {
        // Arrange
        $client = static::createClient();
        
        $filename = 'test-image.gif';

        $this->blobServiceMock
            ->expects($this->once())
            ->method('generateUploadUrl')
            ->with($filename, 3600)
            ->willThrowException(new \Exception('Azure service unavailable'));

        $client->getContainer()->set(AzureBlobImageService::class, $this->blobServiceMock);

        // Act
        $client->request('POST', '/api/images/upload-url', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'filename' => $filename,
            'expiry' => 3600
        ]));

        // Assert
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('error', $responseData);
        $this->assertStringContainsString('Azure service unavailable', $responseData['error']);
    }

    public function testDeleteImageSuccess(): void
    {
        // Arrange
        $client = static::createClient();
        
        $filename = 'test-image-to-delete.jpg';

        $this->blobServiceMock
            ->expects($this->once())
            ->method('deleteImage')
            ->with($filename)
            ->willReturn(true);

        $client->getContainer()->set(AzureBlobImageService::class, $this->blobServiceMock);

        // Act
        $client->request('DELETE', '/api/images/' . $filename);

        // Assert
        $this->assertResponseIsSuccessful();
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Image deleted successfully', $responseData['message']);
    }

    public function testDeleteImageNotFound(): void
    {
        // Arrange
        $client = static::createClient();
        
        $filename = 'non-existent-image.jpg';

        $this->blobServiceMock
            ->expects($this->once())
            ->method('deleteImage')
            ->with($filename)
            ->willReturn(false);

        $client->getContainer()->set(AzureBlobImageService::class, $this->blobServiceMock);

        // Act
        $client->request('DELETE', '/api/images/' . $filename);

        // Assert
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('error', $responseData);
    }

    public function testGetImageInfoSuccess(): void
    {
        // Arrange
        $client = static::createClient();
        
        $filename = 'existing-image.jpg';
        $imageInfo = [
            'filename' => $filename,
            'url' => 'https://storage.blob.core.windows.net/container/' . $filename,
            'size' => 1024000,
            'content_type' => 'image/jpeg',
            'last_modified' => '2025-07-01T10:30:00Z'
        ];

        $this->blobServiceMock
            ->expects($this->once())
            ->method('getImageInfo')
            ->with($filename)
            ->willReturn($imageInfo);

        $client->getContainer()->set(AzureBlobImageService::class, $this->blobServiceMock);

        // Act
        $client->request('GET', '/api/images/' . $filename . '/info');

        // Assert
        $this->assertResponseIsSuccessful();
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertEquals($imageInfo, $responseData);
    }

    public function testUploadUrlWithInvalidFilename(): void
    {
        // Arrange
        $client = static::createClient();

        // Act - filename avec caractères interdits
        $client->request('POST', '/api/images/upload-url', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'filename' => '../../../malicious.exe'
        ]));

        // Assert
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('error', $responseData);
        $this->assertStringContainsString('filename', strtolower($responseData['error']));
    }

    public function testUploadUrlWithUnsupportedFileType(): void
    {
        // Arrange
        $client = static::createClient();

        $this->blobServiceMock
            ->expects($this->once())
            ->method('generateUploadUrl')
            ->willThrowException(new \InvalidArgumentException('Unsupported file type'));

        $client->getContainer()->set(AzureBlobImageService::class, $this->blobServiceMock);

        // Act
        $client->request('POST', '/api/images/upload-url', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'filename' => 'document.pdf'
        ]));

        // Assert
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('error', $responseData);
        $this->assertStringContainsString('Unsupported file type', $responseData['error']);
    }
}
