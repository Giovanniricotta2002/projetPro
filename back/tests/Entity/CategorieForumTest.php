<?php

namespace App\Tests\Entity;

use App\Entity\CategorieForum;
use App\Entity\Forum;
use PHPUnit\Framework\TestCase;

class CategorieForumTest extends TestCase
{
    private CategorieForum $categorieForum;

    protected function setUp(): void
    {
        $this->categorieForum = new CategorieForum();
    }

    public function testConstructor(): void
    {
        // Vérifier l'état initial de l'entité
        self::assertNull($this->categorieForum->getId());
        self::assertNull($this->categorieForum->getName());
        self::assertNull($this->categorieForum->getOrdre());
        self::assertNull($this->categorieForum->getForum());
    }

    public function testIdGetter(): void
    {
        // L'ID devrait être null avant la persistance
        self::assertNull($this->categorieForum->getId());
    }

    public function testNameGetterAndSetter(): void
    {
        $name = 'Catégorie Test';
        
        self::assertNull($this->categorieForum->getName());
        
        $result = $this->categorieForum->setName($name);
        self::assertEquals($name, $this->categorieForum->getName());
        self::assertInstanceOf(CategorieForum::class, $result); // Test fluent interface
    }

    public function testNameLength(): void
    {
        $validName = 'Catégorie Valide'; // 16 caractères
        $longName = str_repeat('a', 35); // Plus de 30 caractères
        
        $this->categorieForum->setName($validName);
        self::assertEquals($validName, $this->categorieForum->getName());
        
        // Le nom long devrait pouvoir être défini au niveau de l'entité
        $this->categorieForum->setName($longName);
        self::assertEquals($longName, $this->categorieForum->getName());
    }

    public function testOrdreGetterAndSetter(): void
    {
        $ordre = 5;
        
        self::assertNull($this->categorieForum->getOrdre());
        
        $result = $this->categorieForum->setOrdre($ordre);
        self::assertEquals($ordre, $this->categorieForum->getOrdre());
        self::assertInstanceOf(CategorieForum::class, $result);
    }

    public function testOrdreSmallInt(): void
    {
        // Test avec les limites d'un SMALLINT (-32768 à 32767)
        $validOrdre = 100;
        $maxOrdre = 32767;
        $minOrdre = -32768;
        
        $this->categorieForum->setOrdre($validOrdre);
        self::assertEquals($validOrdre, $this->categorieForum->getOrdre());
        
        $this->categorieForum->setOrdre($maxOrdre);
        self::assertEquals($maxOrdre, $this->categorieForum->getOrdre());
        
        $this->categorieForum->setOrdre($minOrdre);
        self::assertEquals($minOrdre, $this->categorieForum->getOrdre());
    }

    public function testFluentInterface(): void
    {
        // Test que toutes les méthodes setter retournent l'instance
        $name = 'Test Category';
        $ordre = 1;
        $forum = $this->createMock(Forum::class);
        
        $result = $this->categorieForum
            ->setName($name)
            ->setOrdre($ordre)
            ->setForum($forum);
        
        self::assertInstanceOf(CategorieForum::class, $result);
        self::assertEquals($name, $this->categorieForum->getName());
        self::assertEquals($ordre, $this->categorieForum->getOrdre());
        self::assertEquals($forum, $this->categorieForum->getForum());
    }

    public function testForumRelation(): void
    {
        $forum = $this->createMock(Forum::class);
        
        self::assertNull($this->categorieForum->getForum());
        
        $result = $this->categorieForum->setForum($forum);
        self::assertEquals($forum, $this->categorieForum->getForum());
        self::assertInstanceOf(CategorieForum::class, $result);
        
        // Test de suppression de la relation
        $this->categorieForum->setForum(null);
        self::assertNull($this->categorieForum->getForum());
    }

    public function testCompleteCategory(): void
    {
        // Test d'une catégorie complète
        $name = 'Discussions Générales';
        $ordre = 1;
        $forum = $this->createMock(Forum::class);
        
        $this->categorieForum
            ->setName($name)
            ->setOrdre($ordre)
            ->setForum($forum);
        
        // Vérifications
        self::assertEquals($name, $this->categorieForum->getName());
        self::assertEquals($ordre, $this->categorieForum->getOrdre());
        self::assertEquals($forum, $this->categorieForum->getForum());
    }

    public function testEmptyValues(): void
    {
        // Test avec des valeurs vides
        $this->categorieForum->setName('');
        $this->categorieForum->setOrdre(0);
        
        self::assertEquals('', $this->categorieForum->getName());
        self::assertEquals(0, $this->categorieForum->getOrdre());
    }

    public function testNegativeOrder(): void
    {
        // Test avec un ordre négatif
        $negativeOrder = -5;
        
        $this->categorieForum->setOrdre($negativeOrder);
        self::assertEquals($negativeOrder, $this->categorieForum->getOrdre());
    }

    public function testSpecialCharactersInName(): void
    {
        // Test avec des caractères spéciaux
        $nameWithSpecialChars = 'Catégorie & Événements';
        $nameWithEmoji = 'Catégorie 🎉';
        
        $this->categorieForum->setName($nameWithSpecialChars);
        self::assertEquals($nameWithSpecialChars, $this->categorieForum->getName());
        
        $this->categorieForum->setName($nameWithEmoji);
        self::assertEquals($nameWithEmoji, $this->categorieForum->getName());
    }
}
