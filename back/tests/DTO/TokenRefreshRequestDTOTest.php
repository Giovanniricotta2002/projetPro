<?php

namespace App\Tests\DTO;

use App\DTO\TokenRefreshRequestDTO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class TokenRefreshRequestDTOTest extends TestCase
{
    public function testConstructor(): void
    {
        // Arrange
        $refreshToken = 'valid_refresh_token_that_is_long_enough_to_pass_validation_requirements';

        // Act
        $dto = new TokenRefreshRequestDTO(
            refreshToken: $refreshToken
        );

        // Assert
        $this->assertEquals($refreshToken, $dto->refreshToken);
    }

    public function testFromArray(): void
    {
        // Arrange
        $refreshToken = 'valid_refresh_token_that_is_long_enough_to_pass_validation_requirements';
        $data = [
            'refreshToken' => $refreshToken,
        ];

        // Act
        $dto = TokenRefreshRequestDTO::fromArray($data);

        // Assert
        $this->assertEquals($refreshToken, $dto->refreshToken);
    }

    public function testFromArrayWithMissingRefreshToken(): void
    {
        // Arrange
        $data = [];

        // Act
        $dto = TokenRefreshRequestDTO::fromArray($data);

        // Assert
        $this->assertEquals('', $dto->refreshToken);
    }

    public function testFromParameterBag(): void
    {
        // Arrange
        $refreshToken = 'valid_refresh_token_that_is_long_enough_to_pass_validation_requirements';
        $parameterBag = new ParameterBag([
            'refreshToken' => $refreshToken,
        ]);

        // Act
        $dto = TokenRefreshRequestDTO::fromParameterBag($parameterBag);

        // Assert
        $this->assertEquals($refreshToken, $dto->refreshToken);
    }

    public function testFromParameterBagWithMissingToken(): void
    {
        // Arrange
        $parameterBag = new ParameterBag([]);

        // Act
        $dto = TokenRefreshRequestDTO::fromParameterBag($parameterBag);

        // Assert
        $this->assertEquals('', $dto->refreshToken);
    }

    public function testToArray(): void
    {
        // Arrange
        $refreshToken = 'valid_refresh_token_that_is_long_enough_to_pass_validation_requirements';
        $dto = new TokenRefreshRequestDTO(
            refreshToken: $refreshToken
        );

        // Act
        $array = $dto->toArray();

        // Assert
        $expectedArray = [
            'refreshToken' => $refreshToken,
        ];

        $this->assertEquals($expectedArray, $array);
    }

    public function testReadonlyProperty(): void
    {
        // Arrange
        $refreshToken = 'valid_refresh_token_that_is_long_enough_to_pass_validation_requirements';
        $dto = new TokenRefreshRequestDTO(
            refreshToken: $refreshToken
        );

        // Act & Assert
        $reflection = new \ReflectionClass($dto);
        $refreshTokenProperty = $reflection->getProperty('refreshToken');
        $this->assertTrue($refreshTokenProperty->isReadOnly());
    }

    public function testValidationConstraints(): void
    {
        // Arrange & Act
        $dto = new TokenRefreshRequestDTO(
            refreshToken: 'valid_refresh_token_that_is_long_enough_to_pass_validation_requirements'
        );

        // Assert - Vérifier que les contraintes de validation sont présentes
        $reflection = new \ReflectionClass($dto);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();

        $refreshTokenParam = $parameters[0];
        $this->assertEquals('refreshToken', $refreshTokenParam->getName());

        // Vérifier les attributs de validation
        $attributes = $refreshTokenParam->getAttributes();
        $this->assertGreaterThan(0, count($attributes));
    }

    public function testMinimumTokenLength(): void
    {
        // Arrange - Token trop court (moins de 50 caractères selon la contrainte Length)
        $shortToken = 'short_token';

        // Act
        $dto = new TokenRefreshRequestDTO(
            refreshToken: $shortToken
        );

        // Assert - Le DTO peut être créé mais la validation échouera lors de la validation Symfony
        $this->assertEquals($shortToken, $dto->refreshToken);
        $this->assertLessThan(50, strlen($shortToken));
    }

    public function testMaximumTokenLength(): void
    {
        // Arrange - Token très long (plus de 2048 caractères selon la contrainte Length)
        $longToken = str_repeat('a', 2049);

        // Act
        $dto = new TokenRefreshRequestDTO(
            refreshToken: $longToken
        );

        // Assert - Le DTO peut être créé mais la validation échouera lors de la validation Symfony
        $this->assertEquals($longToken, $dto->refreshToken);
        $this->assertGreaterThan(2048, strlen($longToken));
    }

    public function testValidTokenLength(): void
    {
        // Arrange - Token de longueur valide (entre 50 et 2048 caractères)
        $validToken = str_repeat('a', 100); // 100 caractères

        // Act
        $dto = new TokenRefreshRequestDTO(
            refreshToken: $validToken
        );

        // Assert
        $this->assertEquals($validToken, $dto->refreshToken);
        $this->assertGreaterThanOrEqual(50, strlen($validToken));
        $this->assertLessThanOrEqual(2048, strlen($validToken));
    }

    public function testFromArrayWithDifferentKey(): void
    {
        // Arrange - Test avec une clé différente pour vérifier la robustesse
        $data = [
            'refresh_token' => 'valid_token', // snake_case au lieu de camelCase
            'refreshToken' => 'valid_refresh_token_that_is_long_enough_to_pass_validation_requirements',
        ];

        // Act
        $dto = TokenRefreshRequestDTO::fromArray($data);

        // Assert - Doit utiliser 'refreshToken' (camelCase)
        $this->assertEquals($data['refreshToken'], $dto->refreshToken);
    }

    public function testImmutability(): void
    {
        // Arrange
        $originalToken = 'original_refresh_token_that_is_long_enough_to_pass_validation_requirements';
        $dto = new TokenRefreshRequestDTO(
            refreshToken: $originalToken
        );

        // Act & Assert - Les propriétés readonly ne peuvent pas être modifiées après création
        $this->assertEquals($originalToken, $dto->refreshToken);

        // Tentative de modification via réflexion devrait échouer car readonly
        $reflection = new \ReflectionProperty($dto, 'refreshToken');
        $this->assertTrue($reflection->isReadOnly());
    }
}
