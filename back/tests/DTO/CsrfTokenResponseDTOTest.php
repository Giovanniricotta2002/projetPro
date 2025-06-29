<?php

namespace App\Tests\DTO;

use App\DTO\CsrfTokenResponseDTO;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour CsrfTokenResponseDTO.
 */
class CsrfTokenResponseDTOTest extends TestCase
{
    /**
     * Test de création du DTO avec token valide.
     */
    public function testConstructorWithValidToken(): void
    {
        $token = 'abc123def456ghi789';
        $dto = new CsrfTokenResponseDTO($token);

        $this->assertSame($token, $dto->csrfToken);
    }

    /**
     * Test de création du DTO avec token vide.
     */
    public function testConstructorWithEmptyToken(): void
    {
        $dto = new CsrfTokenResponseDTO('');

        $this->assertSame('', $dto->csrfToken);
    }

    /**
     * Test de la factory method create().
     */
    public function testCreateFactoryMethod(): void
    {
        $token = 'test-csrf-token-123';
        $dto = CsrfTokenResponseDTO::create($token);

        $this->assertInstanceOf(CsrfTokenResponseDTO::class, $dto);
        $this->assertSame($token, $dto->csrfToken);
    }

    /**
     * Test de la méthode toArray().
     */
    public function testToArray(): void
    {
        $token = 'my-csrf-token';
        $dto = new CsrfTokenResponseDTO($token);

        $expected = [
            'csrfToken' => $token,
        ];

        $this->assertSame($expected, $dto->toArray());
    }

    /**
     * Test de la méthode toArray() avec token complexe.
     */
    public function testToArrayWithComplexToken(): void
    {
        $token = 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0';
        $dto = new CsrfTokenResponseDTO($token);

        $result = $dto->toArray();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('csrfToken', $result);
        $this->assertSame($token, $result['csrfToken']);
    }

    /**
     * Test que le DTO est readonly.
     */
    public function testDTOIsReadonly(): void
    {
        $dto = new CsrfTokenResponseDTO('test-token');

        // Vérifier que la classe est readonly en tentant d'accéder aux propriétés
        $this->assertObjectHasProperty('csrfToken', $dto);

        // Utilisation de reflection pour vérifier que la classe est readonly
        $reflection = new \ReflectionClass($dto);
        $this->assertTrue($reflection->isReadOnly());
    }

    /**
     * Test avec différents types de tokens.
     *
     * @dataProvider tokenProvider
     */
    public function testWithVariousTokens(string $token): void
    {
        $dto = CsrfTokenResponseDTO::create($token);

        $this->assertSame($token, $dto->csrfToken);
        $this->assertSame(['csrfToken' => $token], $dto->toArray());
    }

    /**
     * Fournisseur de données pour différents types de tokens.
     */
    public static function tokenProvider(): array
    {
        return [
            'token simple' => ['simple-token'],
            'token avec chiffres' => ['token123'],
            'token avec caractères spéciaux' => ['token-with_special.chars'],
            'token long' => [str_repeat('a', 100)],
            'token avec caractères Unicode' => ['token-éàü-测试'],
        ];
    }
}
