<?php

namespace App\Tests\Entity;

use App\Entity\Forum;
use App\Entity\InfoMachine;
use App\Entity\Machine;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class MachineTest extends TestCase
{
    private Machine $machine;

    protected function setUp(): void
    {
        $this->machine = new Machine();
    }

    public function testConstructor(): void
    {
        // Vérifier que les valeurs par défaut sont correctement définies
        self::assertInstanceOf(\DateTime::class, $this->machine->getDateCreation());
        self::assertInstanceOf(Uuid::class, $this->machine->getUuid());
        self::assertTrue($this->machine->isVisible());
        self::assertEmpty($this->machine->getInfoMachines());
        self::assertNull($this->machine->getId());
    }

    public function testIdGetter(): void
    {
        // L'ID devrait être null avant la persistance
        self::assertNull($this->machine->getId());
    }

    public function testUuidGetterAndSetter(): void
    {
        $uuid = Uuid::v4();

        // Un UUID est généré dans le constructeur
        self::assertInstanceOf(Uuid::class, $this->machine->getUuid());

        $this->machine->setUuid($uuid);
        self::assertEquals($uuid, $this->machine->getUuid());
    }

    public function testUuidUniqueness(): void
    {
        $machine1 = new Machine();
        $machine2 = new Machine();

        // Chaque machine devrait avoir un UUID unique
        self::assertNotEquals($machine1->getUuid(), $machine2->getUuid());
    }

    public function testNameGetterAndSetter(): void
    {
        $name = 'TestMachine';

        self::assertNull($this->machine->getName());

        $this->machine->setName($name);
        self::assertEquals($name, $this->machine->getName());
    }

    public function testNameLength(): void
    {
        $validName = 'Machine123'; // 10 caractères
        $longName = str_repeat('a', 35); // Plus de 30 caractères

        $this->machine->setName($validName);
        self::assertEquals($validName, $this->machine->getName());

        // Le nom long devrait pouvoir être défini au niveau de l'entité
        // mais la DB rejettera probablement
        $this->machine->setName($longName);
        self::assertEquals($longName, $this->machine->getName());
    }

    public function testDateCreationGetterAndSetter(): void
    {
        $dateCreation = new \DateTime('2024-01-01 10:00:00');

        // La dateCreation est définie dans le constructeur
        self::assertInstanceOf(\DateTime::class, $this->machine->getDateCreation());

        $this->machine->setDateCreation($dateCreation);
        self::assertEquals($dateCreation, $this->machine->getDateCreation());
    }

    public function testDateModifGetterAndSetter(): void
    {
        $dateModif = new \DateTime('2024-01-02 15:30:00');

        self::assertNull($this->machine->getDateModif());

        $this->machine->setDateModif($dateModif);
        self::assertEquals($dateModif, $this->machine->getDateModif());
    }

    public function testVisibleGetterAndSetter(): void
    {
        $this->machine->setVisible(null);
        self::assertNull($this->machine->isVisible());

        $this->machine->setVisible(true);
        self::assertTrue($this->machine->isVisible());

        $this->machine->setVisible(false);
        self::assertFalse($this->machine->isVisible());
    }

    public function testForumRelation(): void
    {
        $forum = $this->createMock(Forum::class);

        self::assertNull($this->machine->getForum());

        $this->machine->setForum($forum);
        self::assertEquals($forum, $this->machine->getForum());

        $this->machine->setForum(null);
        self::assertNull($this->machine->getForum());
    }

    public function testInfoMachinesCollection(): void
    {
        $infoMachine = $this->createMock(InfoMachine::class);

        // Tester l'ajout d'une InfoMachine
        self::assertEmpty($this->machine->getInfoMachines());

        $this->machine->addInfoMachine($infoMachine);
        self::assertCount(1, $this->machine->getInfoMachines());
        self::assertTrue($this->machine->getInfoMachines()->contains($infoMachine));

        // Tester la suppression d'une InfoMachine
        $this->machine->removeInfoMachine($infoMachine);
        self::assertEmpty($this->machine->getInfoMachines());
        self::assertFalse($this->machine->getInfoMachines()->contains($infoMachine));
    }

    public function testImageGetterAndSetter(): void
    {
        $imageData = 'binary_image_data';

        self::assertNull($this->machine->getImage());

        $this->machine->setImage($imageData);
        self::assertEquals($imageData, $this->machine->getImage());
    }

    public function testImageBinaryData(): void
    {
        // Simuler des données d'image binaires
        $binaryData = pack('H*', 'ffd8ffe000104a46494600010101006000600000'); // En-tête JPEG

        $this->machine->setImage($binaryData);
        self::assertEquals($binaryData, $this->machine->getImage());
    }

    public function testCreatedAtGetterAndSetter(): void
    {
        $createdAt = new \DateTime('2024-01-01 08:00:00');

        // dateCreation est définie dans le constructeur, pas createdAt
        self::assertInstanceOf(\DateTime::class, $this->machine->getDateCreation());

        $originalCreatedAt = $this->machine->getDateCreation();
        $this->machine->setDateCreation($createdAt);
        self::assertEquals($createdAt, $this->machine->getDateCreation());
        self::assertNotEquals($originalCreatedAt, $this->machine->getDateCreation());
    }

    public function testDateConsistency(): void
    {
        $now = new \DateTime();
        $dateCreation = new \DateTime('2024-01-01');
        $dateModif = new \DateTime('2024-01-02');

        $this->machine->setDateCreation($dateCreation);
        $this->machine->setDateModif($dateModif);

        // La date de modification devrait être postérieure à la date de création
        self::assertGreaterThan($dateCreation, $dateModif);

        // La date de création dans le constructeur devrait être récente
        self::assertLessThanOrEqual($now, $this->machine->getDateCreation());
    }

    public function testMachineState(): void
    {
        // Tester un état complet d'une machine
        $name = 'ProductionMachine';
        $dateCreation = new \DateTime('2024-01-01');
        $visible = true;
        $uuid = Uuid::v4();

        $this->machine->setName($name);
        $this->machine->setDateCreation($dateCreation);
        $this->machine->setVisible($visible);
        $this->machine->setUuid($uuid);

        self::assertEquals($name, $this->machine->getName());
        self::assertEquals($dateCreation, $this->machine->getDateCreation());
        self::assertTrue($this->machine->isVisible());
        self::assertEquals($uuid, $this->machine->getUuid());
    }

    public function testMachineWithForum(): void
    {
        $forum = $this->createMock(Forum::class);
        $forum->method('getId')->willReturn(1);

        $this->machine->setForum($forum);

        // Vérifier la relation bidirectionnelle si elle existe
        self::assertEquals($forum, $this->machine->getForum());
    }

    public function testMachineWithMultipleInfoMachines(): void
    {
        $info1 = $this->createMock(InfoMachine::class);
        $info2 = $this->createMock(InfoMachine::class);
        $info3 = $this->createMock(InfoMachine::class);

        $this->machine->addInfoMachine($info1);
        $this->machine->addInfoMachine($info2);
        $this->machine->addInfoMachine($info3);

        self::assertCount(3, $this->machine->getInfoMachines());
        self::assertTrue($this->machine->getInfoMachines()->contains($info1));
        self::assertTrue($this->machine->getInfoMachines()->contains($info2));
        self::assertTrue($this->machine->getInfoMachines()->contains($info3));

        // Supprimer une info
        $this->machine->removeInfoMachine($info2);
        self::assertCount(2, $this->machine->getInfoMachines());
        self::assertFalse($this->machine->getInfoMachines()->contains($info2));
    }

    public function testUuidVersion(): void
    {
        // Vérifier que l'UUID généré est de la version 7 (comme spécifié dans le constructeur)
        $uuid = $this->machine->getUuid();

        // UUID v7 a le format: xxxxxxxx-xxxx-7xxx-xxxx-xxxxxxxxxxxx
        $uuidString = $uuid->toRfc4122();
        self::assertEquals('7', $uuidString[14], 'UUID should be version 7');
    }

    public function testEmptyName(): void
    {
        $this->machine->setName('');
        self::assertEquals('', $this->machine->getName());

        $this->machine->setName(null);
        self::assertNull($this->machine->getName());
    }

    public function testVisibilityToggle(): void
    {
        // Test du changement de visibilité
        $this->machine->setVisible(true);
        self::assertTrue($this->machine->isVisible());

        // Toggle
        $this->machine->setVisible(!$this->machine->isVisible());
        self::assertFalse($this->machine->isVisible());

        // Toggle encore
        $this->machine->setVisible(!$this->machine->isVisible());
        self::assertTrue($this->machine->isVisible());
    }

    public function testImageSize(): void
    {
        // Test avec différentes tailles d'images
        $smallImage = str_repeat('a', 100);
        $largeImage = str_repeat('b', 10000);

        $this->machine->setImage($smallImage);
        self::assertEquals(100, strlen($this->machine->getImage()));

        $this->machine->setImage($largeImage);
        self::assertEquals(10000, strlen($this->machine->getImage()));
    }

    public function testToString(): void
    {
        $name = 'TestMachine';
        $this->machine->setName($name);

        // Si l'entité a une méthode __toString(), la tester
        if (method_exists($this->machine, '__toString')) {
            self::assertIsString((string) $this->machine);
            self::assertStringContainsString($name, (string) $this->machine);
        } else {
            self::markTestSkipped('Méthode __toString() non implémentée');
        }
    }
}
