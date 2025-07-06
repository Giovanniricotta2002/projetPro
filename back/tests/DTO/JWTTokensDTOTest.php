<?php

namespace App\Tests\DTO;

use App\DTO\JWTTokensDTO;
use PHPUnit\Framework\TestCase;

class JWTTokensDTOTest extends TestCase
{
    public function testConstructor(): void
    {
        // Arrange
        $accessToken = 'access_token_123';
        $refreshToken = 'refresh_token_456';
        $tokenType = 'Bearer';
        $expiresIn = 3600;
        $refreshExpiresIn = 604800;

        // Act
        $dto = new JWTTokensDTO(
            accessToken: $accessToken,
            refreshToken: $refreshToken,
            tokenType: $tokenType,
            expiresIn: $expiresIn,
            refreshExpiresIn: $refreshExpiresIn
        );

        // Assert
        $this->assertEquals($accessToken, $dto->accessToken);
        $this->assertEquals($refreshToken, $dto->refreshToken);
        $this->assertEquals($tokenType, $dto->tokenType);
        $this->assertEquals($expiresIn, $dto->expiresIn);
        $this->assertEquals($refreshExpiresIn, $dto->refreshExpiresIn);
    }

    public function testConstructorWithoutRefreshExpiresIn(): void
    {
        // Arrange
        $accessToken = 'access_token_123';
        $refreshToken = 'refresh_token_456';
        $tokenType = 'Bearer';
        $expiresIn = 3600;

        // Act
        $dto = new JWTTokensDTO(
            accessToken: $accessToken,
            refreshToken: $refreshToken,
            tokenType: $tokenType,
            expiresIn: $expiresIn
        );

        // Assert
        $this->assertEquals($accessToken, $dto->accessToken);
        $this->assertEquals($refreshToken, $dto->refreshToken);
        $this->assertEquals($tokenType, $dto->tokenType);
        $this->assertEquals($expiresIn, $dto->expiresIn);
        $this->assertNull($dto->refreshExpiresIn);
    }

    public function testFromArrayComplete(): void
    {
        // Arrange
        $tokensArray = [
            'access_token' => 'access_token_123',
            'refresh_token' => 'refresh_token_456',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'refresh_expires_in' => 604800
        ];

        // Act
        $dto = JWTTokensDTO::fromArray($tokensArray);

        // Assert
        $this->assertEquals($tokensArray['access_token'], $dto->accessToken);
        $this->assertEquals($tokensArray['refresh_token'], $dto->refreshToken);
        $this->assertEquals($tokensArray['token_type'], $dto->tokenType);
        $this->assertEquals($tokensArray['expires_in'], $dto->expiresIn);
        $this->assertEquals($tokensArray['refresh_expires_in'], $dto->refreshExpiresIn);
    }

    public function testFromArrayWithoutRefreshExpiresIn(): void
    {
        // Arrange
        $tokensArray = [
            'access_token' => 'access_token_123',
            'refresh_token' => 'refresh_token_456',
            'token_type' => 'Bearer',
            'expires_in' => 3600
        ];

        // Act
        $dto = JWTTokensDTO::fromArray($tokensArray);

        // Assert
        $this->assertEquals($tokensArray['access_token'], $dto->accessToken);
        $this->assertEquals($tokensArray['refresh_token'], $dto->refreshToken);
        $this->assertEquals($tokensArray['token_type'], $dto->tokenType);
        $this->assertEquals($tokensArray['expires_in'], $dto->expiresIn);
        $this->assertNull($dto->refreshExpiresIn);
    }

    public function testToArray(): void
    {
        // Arrange
        $dto = new JWTTokensDTO(
            accessToken: 'access_token_123',
            refreshToken: 'refresh_token_456',
            tokenType: 'Bearer',
            expiresIn: 3600,
            refreshExpiresIn: 604800
        );

        // Act
        $array = $dto->toArray();

        // Assert
        $expectedArray = [
            'access_token' => 'access_token_123',
            'refresh_token' => 'refresh_token_456',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'refresh_expires_in' => 604800
        ];

        $this->assertEquals($expectedArray, $array);
    }

    public function testToArrayWithoutRefreshExpiresIn(): void
    {
        // Arrange
        $dto = new JWTTokensDTO(
            accessToken: 'access_token_123',
            refreshToken: 'refresh_token_456',
            tokenType: 'Bearer',
            expiresIn: 3600
        );

        // Act
        $array = $dto->toArray();

        // Assert
        $expectedArray = [
            'access_token' => 'access_token_123',
            'refresh_token' => 'refresh_token_456',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'refresh_expires_in' => null
        ];

        $this->assertEquals($expectedArray, $array);
    }

    public function testFromArrayMissingRequiredFields(): void
    {
        // Arrange
        $incompleteArray = [
            'access_token' => 'access_token_123',
            // refresh_token manquant
            'token_type' => 'Bearer',
            'expires_in' => 3600
        ];

        // Act & Assert
        $this->expectException(\TypeError::class);
        JWTTokensDTO::fromArray($incompleteArray);
    }

    public function testReadonlyProperties(): void
    {
        // Arrange
        $dto = new JWTTokensDTO(
            accessToken: 'access_token_123',
            refreshToken: 'refresh_token_456',
            tokenType: 'Bearer',
            expiresIn: 3600
        );

        // Act & Assert - Les propriétés readonly ne peuvent pas être modifiées
        $reflection = new \ReflectionClass($dto);
        $this->assertTrue($reflection->isReadOnly());
        
        // Vérifier que toutes les propriétés sont readonly
        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property->isReadOnly());
        }
    }

    public function testTokenTypeValidation(): void
    {
        // Arrange & Act
        $dto = new JWTTokensDTO(
            accessToken: 'access_token_123',
            refreshToken: 'refresh_token_456',
            tokenType: 'Bearer',
            expiresIn: 3600
        );

        // Assert
        $this->assertEquals('Bearer', $dto->tokenType);
        
        // Test avec d'autres types de tokens
        $dto2 = new JWTTokensDTO(
            accessToken: 'access_token_123',
            refreshToken: 'refresh_token_456',
            tokenType: 'Basic',
            expiresIn: 3600
        );
        
        $this->assertEquals('Basic', $dto2->tokenType);
    }

    public function testExpiresInValidation(): void
    {
        // Arrange & Act
        $dto = new JWTTokensDTO(
            accessToken: 'access_token_123',
            refreshToken: 'refresh_token_456',
            tokenType: 'Bearer',
            expiresIn: 0
        );

        // Assert
        $this->assertEquals(0, $dto->expiresIn);
        
        // Test avec valeur négative
        $dto2 = new JWTTokensDTO(
            accessToken: 'access_token_123',
            refreshToken: 'refresh_token_456',
            tokenType: 'Bearer',
            expiresIn: -1
        );
        
        $this->assertEquals(-1, $dto2->expiresIn);
    }
}
