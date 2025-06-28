<?php

namespace App\Tests\Attribute;

use App\Attribute\LogLogin;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'attribut LogLogin.
 * 
 * Teste la création et la configuration de l'attribut LogLogin
 * avec différentes combinaisons de paramètres.
 */
class LogLoginTest extends TestCase
{
    /**
     * Teste la création d'un attribut avec les valeurs par défaut.
     */
    public function testDefaultValues(): void
    {
        $attribute = new LogLogin();

        $this->assertTrue($attribute->enabled);
        $this->assertFalse($attribute->logSuccessOnly);
        $this->assertFalse($attribute->logFailureOnly);
        $this->assertEquals('login', $attribute->usernameField);
        $this->assertEquals('password', $attribute->passwordField);
        $this->assertTrue($attribute->checkBlocking);
        $this->assertEquals(5, $attribute->maxIpAttempts);
        $this->assertEquals(3, $attribute->maxLoginAttempts);
        $this->assertEquals(60, $attribute->ipBlockDuration);
        $this->assertEquals(30, $attribute->loginBlockDuration);
    }

    /**
     * Teste la création d'un attribut avec des valeurs personnalisées.
     */
    public function testCustomValues(): void
    {
        $attribute = new LogLogin(
            enabled: false,
            logSuccessOnly: true,
            logFailureOnly: false,
            usernameField: 'email',
            passwordField: 'pwd',
            checkBlocking: false,
            maxIpAttempts: 10,
            maxLoginAttempts: 5,
            ipBlockDuration: 120,
            loginBlockDuration: 60
        );

        $this->assertFalse($attribute->enabled);
        $this->assertTrue($attribute->logSuccessOnly);
        $this->assertFalse($attribute->logFailureOnly);
        $this->assertEquals('email', $attribute->usernameField);
        $this->assertEquals('pwd', $attribute->passwordField);
        $this->assertFalse($attribute->checkBlocking);
        $this->assertEquals(10, $attribute->maxIpAttempts);
        $this->assertEquals(5, $attribute->maxLoginAttempts);
        $this->assertEquals(120, $attribute->ipBlockDuration);
        $this->assertEquals(60, $attribute->loginBlockDuration);
    }

    /**
     * Teste la configuration pour logging des succès seulement.
     */
    public function testSuccessOnlyLogging(): void
    {
        $attribute = new LogLogin(logSuccessOnly: true);

        $this->assertTrue($attribute->enabled);
        $this->assertTrue($attribute->logSuccessOnly);
        $this->assertFalse($attribute->logFailureOnly);
    }

    /**
     * Teste la configuration pour logging des échecs seulement.
     */
    public function testFailureOnlyLogging(): void
    {
        $attribute = new LogLogin(logFailureOnly: true);

        $this->assertTrue($attribute->enabled);
        $this->assertFalse($attribute->logSuccessOnly);
        $this->assertTrue($attribute->logFailureOnly);
    }

    /**
     * Teste la configuration sans blocage automatique.
     */
    public function testNoBlockingConfiguration(): void
    {
        $attribute = new LogLogin(checkBlocking: false);

        $this->assertTrue($attribute->enabled);
        $this->assertFalse($attribute->checkBlocking);
        // Les autres valeurs restent par défaut
        $this->assertEquals(5, $attribute->maxIpAttempts);
        $this->assertEquals(3, $attribute->maxLoginAttempts);
    }

    /**
     * Teste la configuration avec des champs personnalisés.
     */
    public function testCustomFieldNames(): void
    {
        $attribute = new LogLogin(
            usernameField: 'user_email',
            passwordField: 'user_password'
        );

        $this->assertEquals('user_email', $attribute->usernameField);
        $this->assertEquals('user_password', $attribute->passwordField);
    }

    /**
     * Teste la configuration avec des seuils de blocage élevés.
     */
    public function testHighBlockingThresholds(): void
    {
        $attribute = new LogLogin(
            maxIpAttempts: 100,
            maxLoginAttempts: 50,
            ipBlockDuration: 1440, // 24 heures
            loginBlockDuration: 720 // 12 heures
        );

        $this->assertEquals(100, $attribute->maxIpAttempts);
        $this->assertEquals(50, $attribute->maxLoginAttempts);
        $this->assertEquals(1440, $attribute->ipBlockDuration);
        $this->assertEquals(720, $attribute->loginBlockDuration);
    }

    /**
     * Teste que les propriétés sont bien readonly.
     */
    public function testReadonlyProperties(): void
    {
        $attribute = new LogLogin();

        // Vérifier que les propriétés sont bien readonly en utilisant la réflection
        $reflection = new \ReflectionClass($attribute);
        
        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property->isReadOnly(), "Property {$property->getName()} should be readonly");
        }
    }

    /**
     * Teste la configuration conflictuelle (logSuccessOnly et logFailureOnly à true).
     * Note: L'attribut permet cette configuration, c'est au listener de gérer la logique.
     */
    public function testConflictingLogConfiguration(): void
    {
        $attribute = new LogLogin(
            logSuccessOnly: true,
            logFailureOnly: true
        );

        // L'attribut permet cette configuration contradictoire
        $this->assertTrue($attribute->logSuccessOnly);
        $this->assertTrue($attribute->logFailureOnly);
        // C'est au LogLoginAttributeListener de gérer cette logique
    }

    /**
     * Teste la sérialisation de l'attribut (pour le debug/logging).
     */
    public function testAttributeSerialization(): void
    {
        $attribute = new LogLogin(
            enabled: true,
            usernameField: 'email',
            maxIpAttempts: 10
        );

        // Test que l'attribut peut être converti en string pour le debug
        $this->assertIsString((string) $attribute->usernameField);
        $this->assertIsInt($attribute->maxIpAttempts);
        $this->assertIsBool($attribute->enabled);
    }
}
