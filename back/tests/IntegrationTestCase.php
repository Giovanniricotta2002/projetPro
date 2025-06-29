<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Classe de base pour les tests d'intégration.
 */
abstract class IntegrationTestCase extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Démarre le kernel pour les tests
        self::bootKernel(['environment' => 'test']);
    }

    /**
     * Obtient un service depuis le container de test.
     */
    protected function getService(string $serviceId): object
    {
        return static::getContainer()->get($serviceId);
    }

    /**
     * Obtient l'entity manager pour les tests.
     */
    protected function getEntityManager(): object
    {
        return $this->getService('doctrine.orm.entity_manager');
    }
}
