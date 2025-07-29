<?php

namespace App\Tests\Entity;

use App\Entity\{InfoMachine, Machine};
use PHPUnit\Framework\TestCase;

class InfoMachineTest extends TestCase
{
    private InfoMachine $infoMachine;

    protected function setUp(): void
    {
        $this->infoMachine = new InfoMachine();
    }

    public function testConstructor(): void
    {
        // Vérifier l'état initial de l'entité
        self::assertNull($this->infoMachine->getId());
        self::assertNull($this->infoMachine->getText());
        self::assertNull($this->infoMachine->getType());
        self::assertNull($this->infoMachine->getMachine());
    }

    public function testIdGetter(): void
    {
        // L'ID devrait être null avant la persistance
        self::assertNull($this->infoMachine->getId());
    }

    public function testTextGetterAndSetter(): void
    {
        $text = 'Informations détaillées sur la machine';

        self::assertNull($this->infoMachine->getText());

        $result = $this->infoMachine->setText($text);
        self::assertEquals($text, $this->infoMachine->getText());
        self::assertInstanceOf(InfoMachine::class, $result); // Test fluent interface
    }

    public function testTextLongContent(): void
    {
        // Test avec un texte très long (TEXT type)
        $longText = str_repeat('Lorem ipsum dolor sit amet, consectetur adipiscing elit. ', 200);

        $this->infoMachine->setText($longText);
        self::assertEquals($longText, $this->infoMachine->getText());
        self::assertGreaterThan(1000, strlen($this->infoMachine->getText()));
    }

    public function testTextWithSpecialCharacters(): void
    {
        // Test avec des caractères spéciaux et HTML
        $specialText = 'Texte avec caractères spéciaux: àéèùç & "quotes" <script>alert("test")</script>';

        $this->infoMachine->setText($specialText);
        self::assertEquals($specialText, $this->infoMachine->getText());
    }

    public function testTextWithLineBreaks(): void
    {
        // Test avec des retours à la ligne
        $textWithBreaks = "Ligne 1\nLigne 2\r\nLigne 3\n\nLigne 5";

        $this->infoMachine->setText($textWithBreaks);
        self::assertEquals($textWithBreaks, $this->infoMachine->getText());
    }

    public function testTypeGetterAndSetter(): void
    {
        $type = 'specification';

        self::assertNull($this->infoMachine->getType());

        $result = $this->infoMachine->setType($type);
        self::assertEquals($type, $this->infoMachine->getType());
        self::assertInstanceOf(InfoMachine::class, $result);
    }

    public function testTypeLength(): void
    {
        $validType = 'config'; // Moins de 30 caractères
        $longType = str_repeat('type_', 10); // Plus de 30 caractères

        $this->infoMachine->setType($validType);
        self::assertEquals($validType, $this->infoMachine->getType());

        $this->infoMachine->setType($longType);
        self::assertEquals($longType, $this->infoMachine->getType());
    }

    public function testTypeValues(): void
    {
        // Test avec différents types d'informations
        $types = [
            'specification',
            'maintenance',
            'configuration',
            'documentation',
            'troubleshooting',
            'manual',
            'warranty',
            'contact',
        ];

        foreach ($types as $type) {
            $this->infoMachine->setType($type);
            self::assertEquals($type, $this->infoMachine->getType());
        }
    }

    public function testMachineRelation(): void
    {
        $machine = $this->createMock(Machine::class);

        self::assertNull($this->infoMachine->getMachine());

        $result = $this->infoMachine->setMachine($machine);
        self::assertEquals($machine, $this->infoMachine->getMachine());
        self::assertInstanceOf(InfoMachine::class, $result);

        // Test de suppression de la relation
        $this->infoMachine->setMachine(null);
        self::assertNull($this->infoMachine->getMachine());
    }

    public function testFluentInterface(): void
    {
        // Test que toutes les méthodes setter retournent l'instance
        $text = 'Information de test';
        $type = 'test';
        $machine = $this->createMock(Machine::class);

        $result = $this->infoMachine
            ->setText($text)
            ->setType($type)
            ->setMachine($machine);

        self::assertInstanceOf(InfoMachine::class, $result);
        self::assertEquals($text, $this->infoMachine->getText());
        self::assertEquals($type, $this->infoMachine->getType());
        self::assertEquals($machine, $this->infoMachine->getMachine());
    }

    public function testCompleteInfoMachine(): void
    {
        // Test d'une InfoMachine complète
        $text = 'Machine de production haute performance avec système de refroidissement intégré';
        $type = 'specification';
        $machine = $this->createMock(Machine::class);

        $this->infoMachine
            ->setText($text)
            ->setType($type)
            ->setMachine($machine);

        // Vérifications
        self::assertEquals($text, $this->infoMachine->getText());
        self::assertEquals($type, $this->infoMachine->getType());
        self::assertEquals($machine, $this->infoMachine->getMachine());
    }

    public function testEmptyValues(): void
    {
        // Test avec des valeurs vides
        $this->infoMachine->setText('');
        $this->infoMachine->setType('');

        self::assertEquals('', $this->infoMachine->getText());
        self::assertEquals('', $this->infoMachine->getType());
    }

    public function testTextFormats(): void
    {
        // Test avec différents formats de texte
        $formats = [
            'Plain text information',
            'JSON: {"config": "value", "status": "active"}',
            'XML: <config><status>active</status></config>',
            'Markdown: # Title\n## Subtitle\n- Item 1\n- Item 2',
            'HTML: <h1>Title</h1><p>Paragraph</p>',
            'CSV: header1,header2\nvalue1,value2',
        ];

        foreach ($formats as $format) {
            $this->infoMachine->setText($format);
            self::assertEquals($format, $this->infoMachine->getText());
        }
    }

    public function testTypeConventions(): void
    {
        // Test avec des conventions de nommage
        $types = [
            'snake_case',
            'kebab-case',
            'camelCase',
            'PascalCase',
            'UPPER_CASE',
        ];

        foreach ($types as $type) {
            $this->infoMachine->setType($type);
            self::assertEquals($type, $this->infoMachine->getType());
        }
    }

    public function testUnicodeText(): void
    {
        // Test avec du texte Unicode
        $unicodeText = 'Emoji: 🚀🔧⚙️ | Chinese: 机器信息 | Arabic: معلومات الآلة | Russian: Информация о машине';

        $this->infoMachine->setText($unicodeText);
        self::assertEquals($unicodeText, $this->infoMachine->getText());
    }

    public function testSqlInjectionProtection(): void
    {
        // Test avec du contenu potentiellement malicieux
        $maliciousText = "'; DROP TABLE machines; --";
        $maliciousType = "'; DROP TABLE info_machines; --";

        $this->infoMachine->setText($maliciousText);
        $this->infoMachine->setType($maliciousType);

        // Les valeurs devraient être stockées telles quelles (protection au niveau ORM)
        self::assertEquals($maliciousText, $this->infoMachine->getText());
        self::assertEquals($maliciousType, $this->infoMachine->getType());
    }

    public function testJsonContent(): void
    {
        // Test avec du contenu JSON structuré
        $jsonContent = json_encode([
            'specifications' => [
                'power' => '1000W',
                'voltage' => '220V',
                'dimensions' => '100x50x75 cm',
            ],
            'maintenance' => [
                'last_service' => '2024-01-15',
                'next_service' => '2024-07-15',
                'responsible' => 'Jean Dupont',
            ],
        ]);

        $this->infoMachine->setText($jsonContent);
        self::assertEquals($jsonContent, $this->infoMachine->getText());

        // Vérifier que c'est du JSON valide
        $decoded = json_decode($this->infoMachine->getText(), true);
        self::assertNotNull($decoded);
        self::assertIsArray($decoded);
    }

    public function testLargeContent(): void
    {
        // Test avec un contenu très volumineux
        $largeContent = str_repeat('A', 65536); // 64KB

        $this->infoMachine->setText($largeContent);
        self::assertEquals($largeContent, $this->infoMachine->getText());
        self::assertEquals(65536, strlen($this->infoMachine->getText()));
    }

    public function testBinaryContent(): void
    {
        // Test avec du contenu binaire encodé
        $binaryContent = base64_encode(random_bytes(1024));

        $this->infoMachine->setText($binaryContent);
        self::assertEquals($binaryContent, $this->infoMachine->getText());

        // Vérifier que c'est du base64 valide
        $decoded = base64_decode($this->infoMachine->getText(), true);
        self::assertNotFalse($decoded);
    }

    public function testToString(): void
    {
        $text = 'Test Information';
        $this->infoMachine->setText($text);

        // Si l'entité a une méthode __toString(), la tester
        if (method_exists($this->infoMachine, '__toString')) {
            self::assertIsString((string) $this->infoMachine);
            self::assertStringContainsString($text, (string) $this->infoMachine);
        } else {
            self::markTestSkipped('Méthode __toString() non implémentée');
        }
    }
}
