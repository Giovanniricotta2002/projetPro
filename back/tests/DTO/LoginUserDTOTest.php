<?php

namespace App\Tests\DTO;

use App\DTO\LoginUserDTO;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour LoginUserDTO.
 */
class LoginUserDTOTest extends TestCase
{
    /**
     * Test de création du DTO avec paramètres obligatoires seulement.
     */
    public function testConstructorWithRequiredParameters(): void
    {
        $id = 123;
        $username = 'john.doe';
        $roles = ['ROLE_USER'];

        $dto = new LoginUserDTO($id, $username, $roles);

        $this->assertSame($id, $dto->id);
        $this->assertSame($username, $dto->username);
        $this->assertSame($roles, $dto->roles);
        $this->assertNull($dto->lastVisit);
    }

    /**
     * Test de création du DTO avec tous les paramètres.
     */
    public function testConstructorWithAllParameters(): void
    {
        $id = 456;
        $username = 'jane.doe';
        $roles = ['ROLE_USER', 'ROLE_ADMIN'];
        $lastVisit = '2025-01-15 14:30:00';

        $dto = new LoginUserDTO($id, $username, $roles, $lastVisit);

        $this->assertSame($id, $dto->id);
        $this->assertSame($username, $dto->username);
        $this->assertSame($roles, $dto->roles);
        $this->assertSame($lastVisit, $dto->lastVisit);
    }

    /**
     * Test de création du DTO avec lastVisit null explicite.
     */
    public function testConstructorWithNullLastVisit(): void
    {
        $dto = new LoginUserDTO(789, 'user.test', ['ROLE_USER'], null);

        $this->assertNull($dto->lastVisit);
    }

    /**
     * Test de la méthode toArray() sans lastVisit.
     */
    public function testToArrayWithoutLastVisit(): void
    {
        $id = 123;
        $username = 'john.doe';
        $roles = ['ROLE_USER'];

        $dto = new LoginUserDTO($id, $username, $roles);

        $expected = [
            'id' => $id,
            'username' => $username,
            'roles' => $roles,
        ];

        $this->assertSame($expected, $dto->toArray());
    }

    /**
     * Test de la méthode toArray() avec lastVisit.
     */
    public function testToArrayWithLastVisit(): void
    {
        $id = 456;
        $username = 'jane.doe';
        $roles = ['ROLE_USER', 'ROLE_ADMIN'];
        $lastVisit = '2025-01-15 14:30:00';

        $dto = new LoginUserDTO($id, $username, $roles, $lastVisit);

        $expected = [
            'id' => $id,
            'username' => $username,
            'roles' => $roles,
            'last_visit' => $lastVisit,
        ];

        $this->assertSame($expected, $dto->toArray());
    }

    /**
     * Test de la méthode toArray() avec lastVisit null explicite.
     */
    public function testToArrayWithNullLastVisit(): void
    {
        $dto = new LoginUserDTO(789, 'user.test', ['ROLE_USER'], null);

        $result = $dto->toArray();

        $this->assertArrayNotHasKey('last_visit', $result);
        $this->assertCount(3, $result);
    }

    /**
     * Test avec différents types de rôles.
     */
    public function testWithVariousRoles(): void
    {
        $rolesData = [
            'rôle simple' => ['ROLE_USER'],
            'rôles multiples' => ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_MODERATOR'],
            'rôles vides' => [],
            'rôles avec caractères spéciaux' => ['ROLE_SUPER_ADMIN', 'ROLE_API_USER'],
        ];

        foreach ($rolesData as $testCase => $roles) {
            $dto = new LoginUserDTO(1, 'test', $roles);
            $this->assertSame($roles, $dto->roles, "Échec pour le cas : {$testCase}");
        }
    }

    /**
     * Test que le DTO est readonly.
     */
    public function testDTOIsReadonly(): void
    {
        $dto = new LoginUserDTO(1, 'test', ['ROLE_USER']);

        // Vérifier que la classe est readonly
        $reflection = new \ReflectionClass($dto);
        $this->assertTrue($reflection->isReadOnly());
    }

    /**
     * Test avec différents formats de lastVisit.
     *
     * @dataProvider lastVisitProvider
     */
    public function testWithVariousLastVisitFormats(?string $lastVisit): void
    {
        $dto = new LoginUserDTO(1, 'test', ['ROLE_USER'], $lastVisit);

        $this->assertSame($lastVisit, $dto->lastVisit);

        $array = $dto->toArray();
        if ($lastVisit !== null) {
            $this->assertArrayHasKey('last_visit', $array);
            $this->assertSame($lastVisit, $array['last_visit']);
        } else {
            $this->assertArrayNotHasKey('last_visit', $array);
        }
    }

    /**
     * Fournisseur de données pour différents formats de lastVisit.
     */
    public static function lastVisitProvider(): array
    {
        return [
            'null' => [null],
            'date simple' => ['2025-01-15 14:30:00'],
            'date avec secondes' => ['2025-01-15 14:30:45'],
            'date ISO' => ['2025-01-15T14:30:00Z'],
            'date avec timezone' => ['2025-01-15 14:30:00 +01:00'],
        ];
    }

    /**
     * Test avec des IDs négatifs ou zéro.
     *
     * @dataProvider idProvider
     */
    public function testWithVariousIds(int $id): void
    {
        $dto = new LoginUserDTO($id, 'test', ['ROLE_USER']);

        $this->assertSame($id, $dto->id);
        $this->assertSame($id, $dto->toArray()['id']);
    }

    /**
     * Fournisseur de données pour différents IDs.
     */
    public static function idProvider(): array
    {
        return [
            'ID positif' => [123],
            'ID zéro' => [0],
            'ID négatif' => [-1],
            'ID très grand' => [PHP_INT_MAX],
        ];
    }

    /**
     * Test avec différents noms d'utilisateur.
     *
     * @dataProvider usernameProvider
     */
    public function testWithVariousUsernames(string $username): void
    {
        $dto = new LoginUserDTO(1, $username, ['ROLE_USER']);

        $this->assertSame($username, $dto->username);
        $this->assertSame($username, $dto->toArray()['username']);
    }

    /**
     * Fournisseur de données pour différents noms d'utilisateur.
     */
    public static function usernameProvider(): array
    {
        return [
            'nom simple' => ['john'],
            'nom avec point' => ['john.doe'],
            'nom avec underscore' => ['john_doe'],
            'nom avec tiret' => ['john-doe'],
            'nom avec chiffres' => ['user123'],
            'nom vide' => [''],
            'nom avec caractères spéciaux' => ['user@domain.com'],
            'nom Unicode' => ['utilisateur-éàü'],
        ];
    }
}
