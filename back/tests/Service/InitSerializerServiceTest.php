<?php

namespace App\Tests\Service;

use App\Service\InitSerializerService as ServiceInitSerializerService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

/**
 * Tests unitaires pour InitSerializerService.
 */
class InitSerializerServiceTest extends TestCase
{
    private ServiceInitSerializerService $service;

    protected function setUp(): void
    {
        $this->service = new ServiceInitSerializerService();
    }

    /**
     * Test que le service initialise correctement le serializer.
     */
    public function testSerializerInitialization(): void
    {
        $this->assertInstanceOf(Serializer::class, $this->service->serializer);
    }

    /**
     * Test que le serializer supporte la sérialisation JSON.
     */
    public function testJsonSerialization(): void
    {
        $data = ['name' => 'John', 'age' => 30];

        $json = $this->service->serializer->serialize($data, 'json');

        $this->assertIsString($json);
        $this->assertJson($json);
        $this->assertStringContainsString('John', $json);
        $this->assertStringContainsString('30', $json);
    }

    /**
     * Test que le serializer supporte la sérialisation YAML.
     */
    public function testYamlSerialization(): void
    {
        $data = ['name' => 'Bob', 'active' => true];

        $yaml = $this->service->serializer->serialize($data, 'yaml');

        $this->assertIsString($yaml);
        $this->assertStringContainsString('name: Bob', $yaml);
        $this->assertStringContainsString('active: true', $yaml);
    }

    /**
     * Test que le serializer supporte la sérialisation XML.
     */
    public function testXmlSerialization(): void
    {
        $data = ['name' => 'Charlie', 'role' => 'admin'];

        $xml = $this->service->serializer->serialize($data, 'xml');

        $this->assertIsString($xml);
        $this->assertStringContainsString('<name>Charlie</name>', $xml);
        $this->assertStringContainsString('<role>admin</role>', $xml);
    }

    /**
     * Test que le serializer supporte la sérialisation CSV.
     */
    public function testCsvSerialization(): void
    {
        $data = [
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
        ];

        $csv = $this->service->serializer->serialize($data, 'csv');

        $this->assertIsString($csv);
        $this->assertStringContainsString('name,age', $csv);
        $this->assertStringContainsString('John,30', $csv);
        $this->assertStringContainsString('Jane,25', $csv);
    }

    /**
     * Test avec des objets complexes.
     */
    public function testComplexObjectSerialization(): void
    {
        $object = new class {
            public string $name = 'Test Object';
            public array $data = ['key' => 'value'];
            public bool $active = true;
        };

        $json = $this->service->serializer->serialize($object, 'json');

        $this->assertIsString($json);
        $this->assertJson($json);

        $decoded = json_decode($json, true);
        $this->assertSame('Test Object', $decoded['name']);
        $this->assertSame(['key' => 'value'], $decoded['data']);
        $this->assertTrue($decoded['active']);
    }

    /**
     * Test de normalisation d'objets.
     */
    public function testObjectNormalization(): void
    {
        $object = new class {
            private string $privateProperty = 'private';
            public string $publicProperty = 'public';

            public function getPrivateProperty(): string
            {
                return $this->privateProperty;
            }
        };

        $normalized = $this->service->serializer->normalize($object);

        $this->assertIsArray($normalized);
        $this->assertArrayHasKey('publicProperty', $normalized);
        $this->assertSame('public', $normalized['publicProperty']);
    }

    /**
     * Test que le service peut être réinstancié.
     */
    public function testMultipleInstances(): void
    {
        $service1 = new ServiceInitSerializerService();
        $service2 = new ServiceInitSerializerService();

        $this->assertInstanceOf(ServiceInitSerializerService::class, $service1);
        $this->assertInstanceOf(ServiceInitSerializerService::class, $service2);
        $this->assertInstanceOf(Serializer::class, $service1->serializer);
        $this->assertInstanceOf(Serializer::class, $service2->serializer);

        // Les instances doivent être différentes mais fonctionnelles
        $this->assertNotSame($service1, $service2);
        $this->assertNotSame($service1->serializer, $service2->serializer);
    }

    /**
     * Test des formats supportés par le serializer.
     */
    public function testSupportedFormats(): void
    {
        $supportedFormats = ['json', 'xml', 'yaml', 'csv'];

        foreach ($supportedFormats as $format) {
            $data = ['test' => 'value'];

            // Test de sérialisation pour chaque format
            $serialized = $this->service->serializer->serialize($data, $format);
            $this->assertIsString($serialized, "Échec de sérialisation pour le format: {$format}");
            $this->assertNotEmpty($serialized, "Sérialisation vide pour le format: {$format}");
        }
    }

    /**
     * Test de gestion des erreurs de sérialisation.
     */
    public function testSerializationErrorHandling(): void
    {
        $this->expectException(\Symfony\Component\Serializer\Exception\NotEncodableValueException::class);

        // Tenter de sérialiser vers un format non supporté
        $this->service->serializer->serialize(['test' => 'value'], 'unsupported_format');
    }

    /**
     * Test de gestion des erreurs de désérialisation.
     */
    public function testDeserializationErrorHandling(): void
    {
        $this->expectException(\Symfony\Component\Serializer\Exception\NotEncodableValueException::class);

        // Tenter de désérialiser un JSON invalide
        $this->service->serializer->deserialize('invalid json', 'array', 'json');
    }
}
