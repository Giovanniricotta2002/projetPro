<?php

namespace App\Tests\DTO;

use App\Attribute\LogLogin;
use App\DTO\PendingLoginLogDTO;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour le DTO PendingLoginLogDTO.
 * 
 * Teste la création, l'immutabilité et les méthodes du DTO
 * utilisé pour stocker temporairement les informations de logging.
 */
class PendingLoginLogDTOTest extends TestCase
{
    private LogLogin $defaultAttribute;
    private \DateTime $testDateTime;

    protected function setUp(): void
    {
        $this->defaultAttribute = new LogLogin();
        $this->testDateTime = new \DateTime('2025-01-15 10:30:00');
    }

    /**
     * Teste la création d'un DTO avec le constructeur direct.
     */
    public function testDirectConstruction(): void
    {
        $dto = new PendingLoginLogDTO(
            'testuser',
            $this->defaultAttribute,
            $this->testDateTime
        );

        $this->assertEquals('testuser', $dto->username);
        $this->assertSame($this->defaultAttribute, $dto->attribute);
        $this->assertSame($this->testDateTime, $dto->requestTime);
    }

    /**
     * Teste la création d'un DTO avec la factory method.
     */
    public function testFactoryMethodCreation(): void
    {
        $beforeCreation = new \DateTime();
        
        $dto = PendingLoginLogDTO::create('testuser', $this->defaultAttribute);
        
        $afterCreation = new \DateTime();

        $this->assertEquals('testuser', $dto->username);
        $this->assertSame($this->defaultAttribute, $dto->attribute);
        
        // Vérifier que le timestamp est dans la bonne plage
        $this->assertGreaterThanOrEqual($beforeCreation, $dto->requestTime);
        $this->assertLessThanOrEqual($afterCreation, $dto->requestTime);
    }

    /**
     * Teste la méthode shouldLog avec différentes configurations.
     */
    public function testShouldLogWithDefaultSettings(): void
    {
        $dto = PendingLoginLogDTO::create('testuser', $this->defaultAttribute);

        // Avec les paramètres par défaut, tous les logs sont acceptés
        $this->assertTrue($dto->shouldLog(true));  // Succès
        $this->assertTrue($dto->shouldLog(false)); // Échec
    }

    /**
     * Teste shouldLog avec logSuccessOnly activé.
     */
    public function testShouldLogWithSuccessOnly(): void
    {
        $attribute = new LogLogin(logSuccessOnly: true);
        $dto = PendingLoginLogDTO::create('testuser', $attribute);

        $this->assertTrue($dto->shouldLog(true));   // Succès → logué
        $this->assertFalse($dto->shouldLog(false)); // Échec → pas logué
    }

    /**
     * Teste shouldLog avec logFailureOnly activé.
     */
    public function testShouldLogWithFailureOnly(): void
    {
        $attribute = new LogLogin(logFailureOnly: true);
        $dto = PendingLoginLogDTO::create('testuser', $attribute);

        $this->assertFalse($dto->shouldLog(true)); // Succès → pas logué
        $this->assertTrue($dto->shouldLog(false)); // Échec → logué
    }

    /**
     * Teste shouldLog avec les deux filtres activés (cas contradictoire).
     */
    public function testShouldLogWithBothFilters(): void
    {
        $attribute = new LogLogin(logSuccessOnly: true, logFailureOnly: true);
        $dto = PendingLoginLogDTO::create('testuser', $attribute);

        // Avec les deux filtres, rien n'est logué (logique AND)
        $this->assertFalse($dto->shouldLog(true));  // Succès mais logFailureOnly
        $this->assertFalse($dto->shouldLog(false)); // Échec mais logSuccessOnly
    }

    /**
     * Teste l'immutabilité du DTO (readonly).
     */
    public function testDTOImmutability(): void
    {
        $dto = PendingLoginLogDTO::create('testuser', $this->defaultAttribute);

        // Vérifier que la classe est readonly
        $reflection = new \ReflectionClass($dto);
        $this->assertTrue($reflection->isReadOnly());

        // Vérifier que toutes les propriétés sont readonly
        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property->isReadOnly());
        }
    }

    /**
     * Teste les getters du DTO.
     */
    public function testGetters(): void
    {
        $dto = new PendingLoginLogDTO(
            'testuser',
            $this->defaultAttribute,
            $this->testDateTime
        );

        $this->assertEquals('testuser', $dto->getUsername());
        $this->assertSame($this->defaultAttribute, $dto->getAttribute());
        $this->assertSame($this->testDateTime, $dto->getRequestTime());
    }

    /**
     * Teste avec des noms d'utilisateur spéciaux.
     */
    public function testWithSpecialUsernames(): void
    {
        $specialUsernames = [
            'user@example.com',
            'user.name',
            'user-name',
            'user_name',
            'user123',
            'ütf8-üser'
        ];

        foreach ($specialUsernames as $username) {
            $dto = PendingLoginLogDTO::create($username, $this->defaultAttribute);
            $this->assertEquals($username, $dto->username);
            $this->assertEquals($username, $dto->getUsername());
        }
    }

    /**
     * Teste avec différentes configurations d'attribut.
     */
    public function testWithDifferentAttributes(): void
    {
        $customAttribute = new LogLogin(
            enabled: false,
            usernameField: 'email',
            maxIpAttempts: 10
        );

        $dto = PendingLoginLogDTO::create('testuser', $customAttribute);

        $this->assertSame($customAttribute, $dto->attribute);
        $this->assertEquals('email', $dto->attribute->usernameField);
        $this->assertEquals(10, $dto->attribute->maxIpAttempts);
        $this->assertFalse($dto->attribute->enabled);
    }

    /**
     * Teste la performance de création de DTOs.
     */
    public function testPerformance(): void
    {
        $startTime = microtime(true);
        
        // Créer beaucoup de DTOs pour tester la performance
        for ($i = 0; $i < 1000; $i++) {
            $dto = PendingLoginLogDTO::create("user{$i}", $this->defaultAttribute);
            $this->assertInstanceOf(PendingLoginLogDTO::class, $dto);
        }
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // S'assurer que la création est rapide (moins de 100ms pour 1000 DTOs)
        $this->assertLessThan(0.1, $executionTime, 'DTO creation should be fast');
    }

    /**
     * Teste la compatibilité avec différents fuseaux horaires.
     */
    public function testWithDifferentTimezones(): void
    {
        $originalTimezone = date_default_timezone_get();
        
        try {
            // Tester avec différents fuseaux horaires
            $timezones = ['UTC', 'Europe/Paris', 'America/New_York', 'Asia/Tokyo'];
            
            foreach ($timezones as $timezone) {
                date_default_timezone_set($timezone);
                
                $dto = PendingLoginLogDTO::create('testuser', $this->defaultAttribute);
                $this->assertInstanceOf(\DateTime::class, $dto->requestTime);
                $this->assertEquals($timezone, $dto->requestTime->getTimezone()->getName());
            }
        } finally {
            // Restaurer le fuseau horaire original
            date_default_timezone_set($originalTimezone);
        }
    }
}
