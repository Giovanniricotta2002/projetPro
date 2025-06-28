<?php

namespace App\Tests\DTO;

use App\DTO\LoginSuccessResponseDTO;
use App\DTO\LoginUserDTO;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour LoginSuccessResponseDTO.
 */
class LoginSuccessResponseDTOTest extends TestCase
{
    /**
     * Test de création du DTO avec tous les paramètres.
     */
    public function testConstructorWithAllParameters(): void
    {
        $success = true;
        $message = 'Login successful';
        $userDTO = new LoginUserDTO(123, 'john.doe', ['ROLE_USER']);

        $dto = new LoginSuccessResponseDTO($success, $message, $userDTO);

        $this->assertSame($success, $dto->success);
        $this->assertSame($message, $dto->message);
        $this->assertSame($userDTO, $dto->user);
    }

    /**
     * Test de création du DTO avec success false.
     */
    public function testConstructorWithFailure(): void
    {
        $success = false;
        $message = 'Login failed';
        $userDTO = new LoginUserDTO(456, 'jane.doe', ['ROLE_USER']);

        $dto = new LoginSuccessResponseDTO($success, $message, $userDTO);

        $this->assertFalse($dto->success);
        $this->assertSame($message, $dto->message);
        $this->assertSame($userDTO, $dto->user);
    }

    /**
     * Test de la méthode toArray().
     */
    public function testToArray(): void
    {
        $success = true;
        $message = 'Authentication successful';
        $userDTO = new LoginUserDTO(789, 'user.test', ['ROLE_USER', 'ROLE_ADMIN']);

        $dto = new LoginSuccessResponseDTO($success, $message, $userDTO);

        $expected = [
            'success' => $success,
            'message' => $message,
            'user' => $userDTO->toArray()
        ];

        $this->assertSame($expected, $dto->toArray());
    }

    /**
     * Test de la méthode toArray() avec utilisateur ayant lastVisit.
     */
    public function testToArrayWithUserLastVisit(): void
    {
        $success = true;
        $message = 'Welcome back!';
        $lastVisit = '2025-01-15 14:30:00';
        $userDTO = new LoginUserDTO(111, 'returning.user', ['ROLE_USER'], $lastVisit);

        $dto = new LoginSuccessResponseDTO($success, $message, $userDTO);

        $result = $dto->toArray();

        $this->assertSame($success, $result['success']);
        $this->assertSame($message, $result['message']);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('last_visit', $result['user']);
        $this->assertSame($lastVisit, $result['user']['last_visit']);
    }

    /**
     * Test que le DTO est readonly.
     */
    public function testDTOIsReadonly(): void
    {
        $userDTO = new LoginUserDTO(1, 'test', ['ROLE_USER']);
        $dto = new LoginSuccessResponseDTO(true, 'test', $userDTO);
        
        // Vérifier que la classe est readonly
        $reflection = new \ReflectionClass($dto);
        $this->assertTrue($reflection->isReadOnly());
    }

    /**
     * Test avec différents messages.
     *
     * @dataProvider messageProvider
     */
    public function testWithVariousMessages(string $message): void
    {
        $userDTO = new LoginUserDTO(1, 'test', ['ROLE_USER']);
        $dto = new LoginSuccessResponseDTO(true, $message, $userDTO);
        
        $this->assertSame($message, $dto->message);
        $this->assertSame($message, $dto->toArray()['message']);
    }

    /**
     * Fournisseur de données pour différents messages.
     *
     * @return array
     */
    public static function messageProvider(): array
    {
        return [
            'message simple' => ['Login successful'],
            'message vide' => [''],
            'message long' => [str_repeat('Message très long ', 10)],
            'message avec caractères spéciaux' => ['Connexion réussie ! Bienvenue.'],
            'message Unicode' => ['Połączenie zakończone sukcesem! 成功登录！'],
            'message JSON' => ['{"status": "success", "code": 200}'],
        ];
    }

    /**
     * Test avec différents états de succès.
     *
     * @dataProvider successProvider
     */
    public function testWithVariousSuccessStates(bool $success): void
    {
        $userDTO = new LoginUserDTO(1, 'test', ['ROLE_USER']);
        $dto = new LoginSuccessResponseDTO($success, 'test message', $userDTO);
        
        $this->assertSame($success, $dto->success);
        $this->assertSame($success, $dto->toArray()['success']);
    }

    /**
     * Fournisseur de données pour différents états de succès.
     *
     * @return array
     */
    public static function successProvider(): array
    {
        return [
            'succès' => [true],
            'échec' => [false],
        ];
    }

    /**
     * Test avec différents utilisateurs.
     */
    public function testWithVariousUsers(): void
    {
        $users = [
            'utilisateur simple' => new LoginUserDTO(1, 'simple', ['ROLE_USER']),
            'utilisateur admin' => new LoginUserDTO(2, 'admin', ['ROLE_ADMIN']),
            'utilisateur multiple rôles' => new LoginUserDTO(3, 'multi', ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_MODERATOR']),
            'utilisateur avec dernière visite' => new LoginUserDTO(4, 'visitor', ['ROLE_USER'], '2025-01-15 10:00:00'),
        ];

        foreach ($users as $testCase => $userDTO) {
            $dto = new LoginSuccessResponseDTO(true, 'Test', $userDTO);
            
            $this->assertSame($userDTO, $dto->user, "Échec pour le cas : $testCase");
            
            $array = $dto->toArray();
            $this->assertSame($userDTO->toArray(), $array['user'], "Échec du toArray pour le cas : $testCase");
        }
    }

    /**
     * Test de cohérence des données dans toArray().
     */
    public function testToArrayDataConsistency(): void
    {
        $success = true;
        $message = 'Connexion réussie';
        $userDTO = new LoginUserDTO(
            id: 42,
            username: 'test.user',
            roles: ['ROLE_USER', 'ROLE_TEST'],
            lastVisit: '2025-01-15 16:45:30'
        );

        $dto = new LoginSuccessResponseDTO($success, $message, $userDTO);
        $array = $dto->toArray();

        // Vérifier la structure
        $this->assertIsArray($array);
        $this->assertCount(3, $array);
        $this->assertArrayHasKey('success', $array);
        $this->assertArrayHasKey('message', $array);
        $this->assertArrayHasKey('user', $array);

        // Vérifier les types
        $this->assertIsBool($array['success']);
        $this->assertIsString($array['message']);
        $this->assertIsArray($array['user']);

        // Vérifier les valeurs
        $this->assertSame($success, $array['success']);
        $this->assertSame($message, $array['message']);
        $this->assertSame($userDTO->toArray(), $array['user']);
    }

    /**
     * Test de sérialisation JSON implicite.
     */
    public function testJsonSerialization(): void
    {
        $userDTO = new LoginUserDTO(123, 'json.test', ['ROLE_USER']);
        $dto = new LoginSuccessResponseDTO(true, 'JSON test', $userDTO);

        $json = json_encode($dto->toArray());
        $decoded = json_decode($json, true);

        $this->assertIsString($json);
        $this->assertIsArray($decoded);
        $this->assertSame($dto->toArray(), $decoded);
    }
}
