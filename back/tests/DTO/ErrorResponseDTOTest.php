<?php

namespace App\Tests\DTO;

use App\DTO\ErrorResponseDTO;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour le DTO ErrorResponseDTO.
 *
 * Teste la création et les méthodes du DTO utilisé pour
 * les réponses d'erreur standardisées de l'API.
 */
class ErrorResponseDTOTest extends TestCase
{
    /**
     * Teste la création d'une erreur simple.
     */
    public function testCreateSimpleError(): void
    {
        $dto = ErrorResponseDTO::create('Invalid credentials');

        $this->assertEquals('Invalid credentials', $dto->error);
        $this->assertNull($dto->message);
        $this->assertNull($dto->code);
        $this->assertNull($dto->details);
    }

    /**
     * Teste la création d'une erreur avec message détaillé.
     */
    public function testCreateErrorWithMessage(): void
    {
        $dto = ErrorResponseDTO::withMessage(
            'Invalid credentials',
            'The provided username or password is incorrect'
        );

        $this->assertEquals('Invalid credentials', $dto->error);
        $this->assertEquals('The provided username or password is incorrect', $dto->message);
        $this->assertNull($dto->code);
        $this->assertNull($dto->details);
    }

    /**
     * Teste la création d'une erreur avec code.
     */
    public function testCreateErrorWithCode(): void
    {
        $dto = ErrorResponseDTO::withCode('Authentication failed', 4001);

        $this->assertEquals('Authentication failed', $dto->error);
        $this->assertNull($dto->message);
        $this->assertEquals(4001, $dto->code);
        $this->assertNull($dto->details);
    }

    /**
     * Teste la création d'une erreur complète.
     */
    public function testCreateFullError(): void
    {
        $details = ['field' => 'username', 'reason' => 'too_short'];
        $dto = ErrorResponseDTO::full(
            'Validation failed',
            'The username is too short',
            4002,
            $details
        );

        $this->assertEquals('Validation failed', $dto->error);
        $this->assertEquals('The username is too short', $dto->message);
        $this->assertEquals(4002, $dto->code);
        $this->assertEquals($details, $dto->details);
    }

    /**
     * Teste le constructeur direct avec tous les paramètres.
     */
    public function testDirectConstructor(): void
    {
        $details = ['attempts' => 3, 'max_attempts' => 5];
        $dto = new ErrorResponseDTO(
            'Too many attempts',
            'You have exceeded the maximum number of attempts',
            4003,
            $details
        );

        $this->assertEquals('Too many attempts', $dto->error);
        $this->assertEquals('You have exceeded the maximum number of attempts', $dto->message);
        $this->assertEquals(4003, $dto->code);
        $this->assertEquals($details, $dto->details);
    }

    /**
     * Teste la conversion en tableau pour erreur simple.
     */
    public function testToArraySimple(): void
    {
        $dto = ErrorResponseDTO::create('Simple error');
        $array = $dto->toArray();

        $this->assertEquals(['error' => 'Simple error'], $array);
        $this->assertArrayNotHasKey('message', $array);
        $this->assertArrayNotHasKey('code', $array);
        $this->assertArrayNotHasKey('details', $array);
    }

    /**
     * Teste la conversion en tableau pour erreur avec message.
     */
    public function testToArrayWithMessage(): void
    {
        $dto = ErrorResponseDTO::withMessage('Error', 'Detailed message');
        $array = $dto->toArray();

        $expected = [
            'error' => 'Error',
            'message' => 'Detailed message',
        ];

        $this->assertEquals($expected, $array);
    }

    /**
     * Teste la conversion en tableau pour erreur avec code.
     */
    public function testToArrayWithCode(): void
    {
        $dto = ErrorResponseDTO::withCode('Error', 5000);
        $array = $dto->toArray();

        $expected = [
            'error' => 'Error',
            'code' => 5000,
        ];

        $this->assertEquals($expected, $array);
    }

    /**
     * Teste la conversion en tableau pour erreur complète.
     */
    public function testToArrayFull(): void
    {
        $details = ['field' => 'email', 'format' => 'invalid'];
        $dto = ErrorResponseDTO::full('Validation error', 'Invalid email format', 4004, $details);
        $array = $dto->toArray();

        $expected = [
            'error' => 'Validation error',
            'message' => 'Invalid email format',
            'code' => 4004,
            'details' => $details,
        ];

        $this->assertEquals($expected, $array);
    }

    /**
     * Teste l'immutabilité du DTO.
     */
    public function testImmutability(): void
    {
        $dto = ErrorResponseDTO::create('Test error');

        // Vérifier que la classe est readonly
        $reflection = new \ReflectionClass($dto);
        $this->assertTrue($reflection->isReadOnly());

        // Vérifier que toutes les propriétés sont readonly
        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property->isReadOnly());
        }
    }

    /**
     * Teste avec des caractères spéciaux et UTF-8.
     */
    public function testWithSpecialCharacters(): void
    {
        $error = 'Erreur d\'authentification avec caractères spéciaux: àéèçù';
        $message = 'Message avec émojis: 🚫❌⚠️';

        $dto = ErrorResponseDTO::withMessage($error, $message);

        $this->assertEquals($error, $dto->error);
        $this->assertEquals($message, $dto->message);

        $array = $dto->toArray();
        $this->assertEquals($error, $array['error']);
        $this->assertEquals($message, $array['message']);
    }

    /**
     * Teste avec des valeurs limites.
     */
    public function testWithEdgeCases(): void
    {
        // Test avec chaîne vide
        $dto1 = ErrorResponseDTO::create('');
        $this->assertEquals('', $dto1->error);

        // Test avec code 0
        $dto2 = ErrorResponseDTO::withCode('Error', 0);
        $this->assertEquals(0, $dto2->code);

        // Test avec tableau vide pour details
        $dto3 = ErrorResponseDTO::full('Error', 'Message', 1000, []);
        $this->assertEquals([], $dto3->details);
    }

    /**
     * Teste la sérialisation JSON indirecte via toArray.
     */
    public function testJsonSerialization(): void
    {
        $dto = ErrorResponseDTO::full(
            'API Error',
            'Something went wrong',
            5001,
            ['timestamp' => '2025-01-15T10:30:00Z', 'request_id' => 'req_123']
        );

        $json = json_encode($dto->toArray());
        $decoded = json_decode($json, true);

        $this->assertEquals('API Error', $decoded['error']);
        $this->assertEquals('Something went wrong', $decoded['message']);
        $this->assertEquals(5001, $decoded['code']);
        $this->assertIsArray($decoded['details']);
        $this->assertEquals('req_123', $decoded['details']['request_id']);
    }
}
