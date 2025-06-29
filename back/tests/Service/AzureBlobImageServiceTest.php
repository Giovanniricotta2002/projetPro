<?php

namespace App\Tests\Service;

use App\Service\AzureBlobImageService;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Tests unitaires pour AzureBlobImageService.
 */
class AzureBlobImageServiceTest extends TestCase
{
    private AzureBlobImageService $service;

    protected function setUp(): void
    {
        $this->service = new AzureBlobImageService(
            storageAccount: 'testaccount',
            storageKey: base64_encode('test-key-for-unit-tests'),
            containerName: 'test-images',
            logger: new NullLogger()
        );
    }

    public function testGenerateUploadUrlStructure(): void
    {
        $result = $this->service->generateUploadUrl('test.jpg', 3600);

        $this->assertArrayHasKey('upload_url', $result);
        $this->assertArrayHasKey('blob_name', $result);
        $this->assertArrayHasKey('expires_at', $result);
        $this->assertArrayHasKey('headers', $result);

        // Vérifier la structure de l'URL
        $this->assertStringContainsString('testaccount.blob.core.windows.net', $result['upload_url']);
        $this->assertStringContainsString('test-images/', $result['upload_url']);
        $this->assertStringContainsString('.jpg', $result['blob_name']);
    }

    public function testGetImageUrl(): void
    {
        $blobName = '2025/06/29/test.jpg';
        $url = $this->service->getImageUrl($blobName);

        $expectedUrl = 'https://testaccount.blob.core.windows.net/test-images/2025/06/29/test.jpg';
        $this->assertEquals($expectedUrl, $url);
    }

    public function testGetSignedImageUrl(): void
    {
        $blobName = '2025/06/29/test.jpg';
        $signedUrl = $this->service->getSignedImageUrl($blobName, 3600);

        $this->assertStringContainsString('testaccount.blob.core.windows.net', $signedUrl);
        $this->assertStringContainsString($blobName, $signedUrl);
        $this->assertStringContainsString('sig=', $signedUrl); // Signature SAS
    }

    public function testGenerateUploadUrlWithDifferentExtensions(): void
    {
        $extensions = ['jpg', 'png', 'gif', 'webp'];

        foreach ($extensions as $ext) {
            $result = $this->service->generateUploadUrl("test.{$ext}");

            $this->assertStringContainsString(".{$ext}", $result['blob_name']);
            $this->assertArrayHasKey('x-ms-blob-content-type', $result['headers']);
        }
    }

    public function testBlobNameGeneration(): void
    {
        $result1 = $this->service->generateUploadUrl('test.jpg');
        $result2 = $this->service->generateUploadUrl('test.jpg');

        // Les blob names doivent être uniques
        $this->assertNotEquals($result1['blob_name'], $result2['blob_name']);

        // Mais avec la même structure (année/mois/jour)
        $date = date('Y/m/d');
        $this->assertStringStartsWith($date, $result1['blob_name']);
        $this->assertStringStartsWith($date, $result2['blob_name']);
    }
}
