<?php

namespace App\Tests\DTO;

use App\DTO\AzureUploadResponseDTO;
use PHPUnit\Framework\TestCase;

class AzureUploadResponseDTOTest extends TestCase
{
    public function testConstructor(): void
    {
        // Arrange
        $uploadUrl = 'https://storage.blob.core.windows.net/container/blob?sig=signature';
        $blobName = 'images/test-image.jpg';
        $expiresAt = '2025-07-03T12:00:00Z';
        $headers = [
            'x-ms-blob-type' => 'BlockBlob',
            'Content-Type' => 'image/jpeg',
        ];

        // Act
        $dto = new AzureUploadResponseDTO(
            uploadUrl: $uploadUrl,
            blobName: $blobName,
            expiresAt: $expiresAt,
            headers: $headers
        );

        // Assert
        $this->assertEquals($uploadUrl, $dto->uploadUrl);
        $this->assertEquals($blobName, $dto->blobName);
        $this->assertEquals($expiresAt, $dto->expiresAt);
        $this->assertEquals($headers, $dto->headers);
    }

    public function testToArray(): void
    {
        // Arrange
        $uploadUrl = 'https://storage.blob.core.windows.net/container/blob?sig=signature';
        $blobName = 'images/test-image.jpg';
        $expiresAt = '2025-07-03T12:00:00Z';
        $headers = [
            'x-ms-blob-type' => 'BlockBlob',
            'Content-Type' => 'image/jpeg',
        ];

        $dto = new AzureUploadResponseDTO(
            uploadUrl: $uploadUrl,
            blobName: $blobName,
            expiresAt: $expiresAt,
            headers: $headers
        );

        // Act
        $array = $dto->toArray();

        // Assert
        $expectedArray = [
            'upload_url' => $uploadUrl,
            'blob_name' => $blobName,
            'expires_at' => $expiresAt,
            'headers' => $headers,
        ];

        $this->assertEquals($expectedArray, $array);
    }

    public function testFromServiceData(): void
    {
        // Arrange
        $serviceData = [
            'upload_url' => 'https://storage.blob.core.windows.net/container/blob?sig=signature',
            'blob_name' => 'images/test-image.jpg',
            'expires_at' => '2025-07-03T12:00:00Z',
            'headers' => [
                'x-ms-blob-type' => 'BlockBlob',
                'Content-Type' => 'image/jpeg',
            ],
        ];

        // Act
        $dto = AzureUploadResponseDTO::fromServiceData($serviceData);

        // Assert
        $this->assertEquals($serviceData['upload_url'], $dto->uploadUrl);
        $this->assertEquals($serviceData['blob_name'], $dto->blobName);
        $this->assertEquals($serviceData['expires_at'], $dto->expiresAt);
        $this->assertEquals($serviceData['headers'], $dto->headers);
    }

    public function testIsExpiredWhenNotExpired(): void
    {
        // Arrange - URL qui expire dans 1 heure
        $futureExpiry = (new \DateTime('+1 hour'))->format('Y-m-d\TH:i:s\Z');

        $dto = new AzureUploadResponseDTO(
            uploadUrl: 'https://storage.blob.core.windows.net/container/blob?sig=signature',
            blobName: 'images/test.jpg',
            expiresAt: $futureExpiry,
            headers: []
        );

        // Act & Assert
        $this->assertFalse($dto->isExpired());
    }

    public function testIsExpiredWhenExpired(): void
    {
        // Arrange - URL expirée depuis 1 heure
        $pastExpiry = (new \DateTime('-1 hour'))->format('Y-m-d\TH:i:s\Z');

        $dto = new AzureUploadResponseDTO(
            uploadUrl: 'https://storage.blob.core.windows.net/container/blob?sig=signature',
            blobName: 'images/test.jpg',
            expiresAt: $pastExpiry,
            headers: []
        );

        // Act & Assert
        $this->assertTrue($dto->isExpired());
    }

    public function testGetTimeUntilExpiryWhenNotExpired(): void
    {
        // Arrange - URL qui expire dans 3600 secondes (1 heure)
        $futureExpiry = (new \DateTime('+1 hour'))->format('Y-m-d\TH:i:s\Z');

        $dto = new AzureUploadResponseDTO(
            uploadUrl: 'https://storage.blob.core.windows.net/container/blob?sig=signature',
            blobName: 'images/test.jpg',
            expiresAt: $futureExpiry,
            headers: []
        );

        // Act
        $timeUntilExpiry = $dto->getTimeUntilExpiry();

        // Assert - Doit être environ 3600 secondes (avec une marge d'erreur de quelques secondes)
        $this->assertGreaterThan(3590, $timeUntilExpiry);
        $this->assertLessThan(3610, $timeUntilExpiry);
    }

    public function testGetTimeUntilExpiryWhenExpired(): void
    {
        // Arrange - URL expirée depuis 1 heure
        $pastExpiry = (new \DateTime('-1 hour'))->format('Y-m-d\TH:i:s\Z');

        $dto = new AzureUploadResponseDTO(
            uploadUrl: 'https://storage.blob.core.windows.net/container/blob?sig=signature',
            blobName: 'images/test.jpg',
            expiresAt: $pastExpiry,
            headers: []
        );

        // Act & Assert
        $this->assertEquals(0, $dto->getTimeUntilExpiry());
    }

    public function testReadonlyProperties(): void
    {
        // Arrange
        $dto = new AzureUploadResponseDTO(
            uploadUrl: 'https://storage.blob.core.windows.net/container/blob?sig=signature',
            blobName: 'images/test.jpg',
            expiresAt: '2025-07-03T12:00:00Z',
            headers: []
        );

        // Act & Assert
        $reflection = new \ReflectionClass($dto);
        $this->assertTrue($reflection->isReadOnly());

        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property->isReadOnly());
        }
    }

    public function testValidUploadUrl(): void
    {
        // Arrange & Act
        $dto = new AzureUploadResponseDTO(
            uploadUrl: 'https://storage.blob.core.windows.net/container/blob?sig=signature',
            blobName: 'test.jpg',
            expiresAt: '2025-07-03T12:00:00Z',
            headers: []
        );

        // Assert
        $this->assertStringContainsString('https://', $dto->uploadUrl);
        $this->assertStringContainsString('blob.core.windows.net', $dto->uploadUrl);
        $this->assertStringContainsString('sig=', $dto->uploadUrl);
    }

    public function testHeadersValidation(): void
    {
        // Arrange & Act
        $dto = new AzureUploadResponseDTO(
            uploadUrl: 'https://storage.blob.core.windows.net/container/blob?sig=signature',
            blobName: 'test.jpg',
            expiresAt: '2025-07-03T12:00:00Z',
            headers: [
                'x-ms-blob-type' => 'BlockBlob',
                'Content-Type' => 'image/jpeg',
                'Content-Length' => '1048576',
            ]
        );

        // Assert
        $this->assertIsArray($dto->headers);
        $this->assertArrayHasKey('x-ms-blob-type', $dto->headers);
        $this->assertEquals('BlockBlob', $dto->headers['x-ms-blob-type']);
        $this->assertArrayHasKey('Content-Type', $dto->headers);
        $this->assertEquals('image/jpeg', $dto->headers['Content-Type']);
    }

    public function testExpiresAtFormat(): void
    {
        // Arrange
        $expiresAt = '2025-07-03T12:30:45Z';

        // Act
        $dto = new AzureUploadResponseDTO(
            uploadUrl: 'https://storage.blob.core.windows.net/container/blob?sig=signature',
            blobName: 'test.jpg',
            expiresAt: $expiresAt,
            headers: []
        );

        // Assert
        $this->assertEquals($expiresAt, $dto->expiresAt);

        // Vérifier que c'est un format ISO 8601 valide
        $dateTime = \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $expiresAt);
        $this->assertInstanceOf(\DateTime::class, $dateTime);
    }

    public function testBlobNameValidation(): void
    {
        // Arrange & Act
        $dto = new AzureUploadResponseDTO(
            uploadUrl: 'https://storage.blob.core.windows.net/container/blob?sig=signature',
            blobName: 'images/subfolder/file-name_123.jpg',
            expiresAt: '2025-07-03T12:00:00Z',
            headers: []
        );

        // Assert
        $this->assertIsString($dto->blobName);
        $this->assertStringContainsString('/', $dto->blobName);
        $this->assertStringContainsString('.jpg', $dto->blobName);
    }

    public function testFromServiceDataMissingField(): void
    {
        // Arrange
        $incompleteData = [
            'upload_url' => 'https://storage.blob.core.windows.net/container/blob?sig=signature',
            'blob_name' => 'test.jpg',
            // expires_at manquant
            'headers' => [],
        ];

        // Act & Assert
        $this->expectException(\TypeError::class);
        AzureUploadResponseDTO::fromServiceData($incompleteData);
    }

    public function testEmptyHeaders(): void
    {
        // Arrange & Act
        $dto = new AzureUploadResponseDTO(
            uploadUrl: 'https://storage.blob.core.windows.net/container/blob?sig=signature',
            blobName: 'test.jpg',
            expiresAt: '2025-07-03T12:00:00Z',
            headers: []
        );

        // Assert
        $this->assertIsArray($dto->headers);
        $this->assertEmpty($dto->headers);
    }
}
