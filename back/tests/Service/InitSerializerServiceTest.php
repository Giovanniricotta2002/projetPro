<?php

namespace App\Tests\Service;

use App\Services\InitSerializerService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

/**
 * Tests unitaires pour InitSerializerService.
 */
class InitSerializerServiceTest extends TestCase
{
    private InitSerializerService $service;

    protected function setUp(): void
    {
        $this->service = new InitSerializerService();
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
     * Test que le serializer supporte la désérialisation JSON.
     */
    public function testJsonDeserialization(): void
    {
        $json = '{"name":"Jane","age":25}';

        $data = $this->service->serializer->deserialize($json, 'array', 'json');

        $this->assertIsArray($data);
        $this->assertSame('Jane', $data['name']);
        $this->assertSame(25, $data['age']);
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
     * Test que le serializer supporte la désérialisation YAML.
     */
    public function testYamlDeserialization(): void
    {
        $yaml = "name: Alice\nage: 28";

        $data = $this->service->serializer->deserialize($yaml, 'array', 'yaml');

        $this->assertIsArray($data);
        $this->assertSame('Alice', $data['name']);
        $this->assertSame(28, $data['age']);
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
     * Test que le serializer supporte la désérialisation XML.
     */
    public function testXmlDeserialization(): void
    {
        $xml = '<response><name>David</name><status>active</status></response>';

        $data = $this->service->serializer->deserialize($xml, 'array', 'xml');

        $this->assertIsArray($data);
        $this->assertSame('David', $data['name']);
        $this->assertSame('active', $data['status']);
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
     * Test que le serializer supporte la désérialisation CSV.
     */
    public function testCsvDeserialization(): void
    {
        $csv = "name,age\nBob,35\nAlice,28";

        $data = $this->service->serializer->deserialize($csv, 'array', 'csv');

        $this->assertIsArray($data);
        $this->assertCount(2, $data);
        $this->assertSame('Bob', $data[0]['name']);
        $this->assertSame('35', $data[0]['age']);
        $this->assertSame('Alice', $data[1]['name']);
        $this->assertSame('28', $data[1]['age']);
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
        $service1 = new InitSerializerService();
        $service2 = new InitSerializerService();

        $this->assertInstanceOf(InitSerializerService::class, $service1);
        $this->assertInstanceOf(InitSerializerService::class, $service2);
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
