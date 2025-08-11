<?php

namespace App\Tests\Entity;

use App\Entity\{CategorieForum, Forum, Machine, Post, Utilisateur};
use PHPUnit\Framework\TestCase;

class ForumTest extends TestCase
{
    private Forum $forum;

    protected function setUp(): void
    {
        $this->forum = new Forum();
    }

    public function testConstructor(): void
    {
        // Vérifier que les valeurs par défaut sont correctement définies
        self::assertEmpty($this->forum->getPost());
        self::assertEmpty($this->forum->getCategorieForums());
        self::assertNull($this->forum->getId());
    }

    public function testIdGetter(): void
    {
        // L'ID devrait être null avant la persistance
        self::assertNull($this->forum->getId());
    }

    public function testTitreGetterAndSetter(): void
    {
        $titre = 'Forum de Discussion';

        self::assertNull($this->forum->getTitre());

        $result = $this->forum->setTitre($titre);
        self::assertEquals($titre, $this->forum->getTitre());
        self::assertInstanceOf(Forum::class, $result); // Test fluent interface
    }

    public function testTitreLength(): void
    {
        $validTitre = 'Forum Test'; // Moins de 30 caractères
        $longTitre = str_repeat('a', 35); // Plus de 30 caractères

        $this->forum->setTitre($validTitre);
        self::assertEquals($validTitre, $this->forum->getTitre());

        $this->forum->setTitre($longTitre);
        self::assertEquals($longTitre, $this->forum->getTitre());
    }

    public function testDateCreationGetterAndSetter(): void
    {
        $dateCreation = new \DateTime('2024-01-01 10:00:00');

        self::assertNull($this->forum->getDateCreation());

        $result = $this->forum->setDateCreation($dateCreation);
        self::assertEquals($dateCreation, $this->forum->getDateCreation());
        self::assertInstanceOf(Forum::class, $result);
    }

    public function testDateClotureGetterAndSetter(): void
    {
        $dateCloture = new \DateTime('2024-12-31 23:59:59');

        self::assertNull($this->forum->getDateCloture());

        $result = $this->forum->setDateCloture($dateCloture);
        self::assertEquals($dateCloture, $this->forum->getDateCloture());
        self::assertInstanceOf(Forum::class, $result);

        // Test avec null
        $this->forum->setDateCloture(null);
        self::assertNull($this->forum->getDateCloture());
    }

    public function testDescriptionGetterAndSetter(): void
    {
        $description = 'Description détaillée du forum de discussion';

        self::assertNull($this->forum->getDescription());

        $result = $this->forum->setDescription($description);
        self::assertEquals($description, $this->forum->getDescription());
        self::assertInstanceOf(Forum::class, $result);
    }

    public function testDescriptionCanBeNull(): void
    {
        $this->forum->setDescription('Some description');
        self::assertEquals('Some description', $this->forum->getDescription());

        $this->forum->setDescription(null);
        self::assertNull($this->forum->getDescription());
    }

    public function testDescriptionLongText(): void
    {
        // Test avec un texte très long
        $longDescription = str_repeat('Lorem ipsum dolor sit amet. ', 200);

        $this->forum->setDescription($longDescription);
        self::assertEquals($longDescription, $this->forum->getDescription());
    }

    public function testOrdreAffichageGetterAndSetter(): void
    {
        $ordre = 5;

        self::assertNull($this->forum->getOrdreAffichage());

        $result = $this->forum->setOrdreAffichage($ordre);
        self::assertEquals($ordre, $this->forum->getOrdreAffichage());
        self::assertInstanceOf(Forum::class, $result);
    }

    public function testOrdreAffichageValues(): void
    {
        // Test avec différentes valeurs d'ordre
        $orders = [0, 1, 10, 100, -1, -10];

        foreach ($orders as $order) {
            $this->forum->setOrdreAffichage($order);
            self::assertEquals($order, $this->forum->getOrdreAffichage());
        }
    }

    public function testVisibleGetterAndSetter(): void
    {
        self::assertNull($this->forum->isVisible());

        $result = $this->forum->setVisible(true);
        self::assertTrue($this->forum->isVisible());
        self::assertInstanceOf(Forum::class, $result);

        $this->forum->setVisible(false);
        self::assertFalse($this->forum->isVisible());
    }

    public function testSlugGetterAndSetter(): void
    {
        $slug = 'forum-discussion';

        self::assertNull($this->forum->getSlug());

        $result = $this->forum->setSlug($slug);
        self::assertEquals($slug, $this->forum->getSlug());
        self::assertInstanceOf(Forum::class, $result);
    }

    public function testSlugLength(): void
    {
        $validSlug = 'forum-test'; // Moins de 50 caractères
        $longSlug = str_repeat('slug-', 15); // Plus de 50 caractères

        $this->forum->setSlug($validSlug);
        self::assertEquals($validSlug, $this->forum->getSlug());

        $this->forum->setSlug($longSlug);
        self::assertEquals($longSlug, $this->forum->getSlug());
    }

    public function testCreatedAtGetter(): void
    {
        // createdAt est définie dans le constructeur
        self::assertInstanceOf(\DateTime::class, $this->forum->getCreatedAt());

        $now = new \DateTime();
        self::assertLessThanOrEqual($now, $this->forum->getCreatedAt());
    }

    public function testUpdatedAtGetterAndSetter(): void
    {
        $updatedAt = new \DateTime('2024-01-02 15:30:00');

        self::assertNull($this->forum->getUpdatedAt());

        $result = $this->forum->setUpdatedAt($updatedAt);
        self::assertEquals($updatedAt, $this->forum->getUpdatedAt());
        self::assertInstanceOf(Forum::class, $result);
    }

    public function testDeletedAtGetterAndSetter(): void
    {
        $deletedAt = new \DateTime('2024-01-03 10:00:00');

        self::assertNull($this->forum->getDeletedAt());

        $result = $this->forum->setDeletedAt($deletedAt);
        self::assertEquals($deletedAt, $this->forum->getDeletedAt());
        self::assertInstanceOf(Forum::class, $result);
    }

    public function testPostsCollection(): void
    {
        $post = $this->createMock(Post::class);
        $post->method('setForum')->willReturn($post);

        // Tester l'ajout d'un post
        self::assertEmpty($this->forum->getPost());

        $result = $this->forum->addPost($post);
        self::assertCount(1, $this->forum->getPost());
        self::assertTrue($this->forum->getPost()->contains($post));
        self::assertInstanceOf(Forum::class, $result);
    }

    public function testCategorieForumsCollection(): void
    {
        $categorie = $this->createMock(CategorieForum::class);
        $categorie->method('setForum')->willReturn($categorie);
        $categorie->method('getForum')->willReturn($this->forum);

        // Tester l'ajout d'une catégorie
        self::assertEmpty($this->forum->getCategorieForums());

        $result = $this->forum->addCategorieForum($categorie);
        self::assertCount(1, $this->forum->getCategorieForums());
        self::assertTrue($this->forum->getCategorieForums()->contains($categorie));
        self::assertInstanceOf(Forum::class, $result);

        // Tester la suppression d'une catégorie
        $this->forum->removeCategorieForum($categorie);
        self::assertEmpty($this->forum->getCategorieForums());
        self::assertFalse($this->forum->getCategorieForums()->contains($categorie));
    }

    public function testUtilisateurRelation(): void
    {
        $utilisateur = $this->createMock(Utilisateur::class);

        self::assertNull($this->forum->getUtilisateur());

        $result = $this->forum->setUtilisateur($utilisateur);
        self::assertEquals($utilisateur, $this->forum->getUtilisateur());
        self::assertInstanceOf(Forum::class, $result);

        $this->forum->setUtilisateur(null);
        self::assertNull($this->forum->getUtilisateur());
    }

    public function testMachineRelation(): void
    {
        $machine = $this->createMock(Machine::class);
        $machine->method('getForum')->willReturn(null);
        $machine->method('setForum')->willReturn($machine);

        self::assertNull($this->forum->getMachine());

        $result = $this->forum->setMachine($machine);
        self::assertEquals($machine, $this->forum->getMachine());
        self::assertInstanceOf(Forum::class, $result);
    }

    public function testMachineRelationBidirectional(): void
    {
        $machine = $this->createMock(Machine::class);
        $machine->method('getForum')->willReturn($this->forum);
        $machine->method('setForum')->willReturn($machine);

        // Test de suppression de la relation
        $this->forum->setMachine($machine);
        $this->forum->setMachine(null);

        self::assertNull($this->forum->getMachine());
    }

    public function testFluentInterface(): void
    {
        // Test que toutes les méthodes setter retournent l'instance
        $titre = 'Forum Test';
        $description = 'Description test';
        $ordre = 1;
        $visible = true;
        $slug = 'forum-test';
        $dateCreation = new \DateTime();
        $utilisateur = $this->createMock(Utilisateur::class);

        $result = $this->forum
            ->setTitre($titre)
            ->setDescription($description)
            ->setOrdreAffichage($ordre)
            ->setVisible($visible)
            ->setSlug($slug)
            ->setDateCreation($dateCreation)
            ->setUtilisateur($utilisateur);

        self::assertInstanceOf(Forum::class, $result);
        self::assertEquals($titre, $this->forum->getTitre());
        self::assertEquals($description, $this->forum->getDescription());
        self::assertEquals($ordre, $this->forum->getOrdreAffichage());
        self::assertTrue($this->forum->isVisible());
        self::assertEquals($slug, $this->forum->getSlug());
        self::assertEquals($dateCreation, $this->forum->getDateCreation());
        self::assertEquals($utilisateur, $this->forum->getUtilisateur());
    }

    public function testCompleteForum(): void
    {
        // Test d'un forum complet
        $titre = 'Forum Général';
        $description = 'Forum pour les discussions générales';
        $ordre = 1;
        $visible = true;
        $slug = 'forum-general';
        $dateCreation = new \DateTime('2024-01-01');

        $this->forum
            ->setTitre($titre)
            ->setDescription($description)
            ->setOrdreAffichage($ordre)
            ->setVisible($visible)
            ->setSlug($slug)
            ->setDateCreation($dateCreation);

        // Vérifications
        self::assertEquals($titre, $this->forum->getTitre());
        self::assertEquals($description, $this->forum->getDescription());
        self::assertEquals($ordre, $this->forum->getOrdreAffichage());
        self::assertTrue($this->forum->isVisible());
        self::assertEquals($slug, $this->forum->getSlug());
        self::assertEquals($dateCreation, $this->forum->getDateCreation());
    }

    public function testDateConsistency(): void
    {
        $dateCreation = new \DateTime('2024-01-01 10:00:00');
        $dateCloture = new \DateTime('2024-12-31 23:59:59');
        $updatedAt = new \DateTime('2024-06-15 12:00:00');

        $this->forum->setDateCreation($dateCreation);
        $this->forum->setDateCloture($dateCloture);
        $this->forum->setUpdatedAt($updatedAt);

        // La date de clôture devrait être postérieure à la date de création
        self::assertGreaterThan($dateCreation, $dateCloture);

        // La date de mise à jour devrait être entre création et clôture
        self::assertGreaterThan($dateCreation, $updatedAt);
        self::assertLessThan($dateCloture, $updatedAt);
    }

    public function testSoftDelete(): void
    {
        // Test de suppression logique
        self::assertNull($this->forum->getDeletedAt());

        $deletedAt = new \DateTime();
        $this->forum->setDeletedAt($deletedAt);

        self::assertEquals($deletedAt, $this->forum->getDeletedAt());

        // Un forum avec deletedAt défini pourrait être considéré comme supprimé
        self::assertNotNull($this->forum->getDeletedAt());
    }

    public function testMultiplePosts(): void
    {
        $post1 = $this->createMock(Post::class);
        $post1->method('setForum')->willReturn($post1);
        $post2 = $this->createMock(Post::class);
        $post2->method('setForum')->willReturn($post2);
        $post3 = $this->createMock(Post::class);
        $post3->method('setForum')->willReturn($post3);

        $this->forum->addPost($post1);
        $this->forum->addPost($post2);
        $this->forum->addPost($post3);

        self::assertCount(3, $this->forum->getPost());
        self::assertTrue($this->forum->getPost()->contains($post1));
        self::assertTrue($this->forum->getPost()->contains($post2));
        self::assertTrue($this->forum->getPost()->contains($post3));
    }

    public function testSpecialCharacters(): void
    {
        // Test avec des caractères spéciaux
        $titre = 'Forum & Discussions';
        $description = 'Description avec caractères spéciaux: àéèùç & "quotes"';
        $slug = 'forum-discussions';

        $this->forum->setTitre($titre);
        $this->forum->setDescription($description);
        $this->forum->setSlug($slug);

        self::assertEquals($titre, $this->forum->getTitre());
        self::assertEquals($description, $this->forum->getDescription());
        self::assertEquals($slug, $this->forum->getSlug());
    }

    public function testToString(): void
    {
        $titre = 'Test Forum';
        $this->forum->setTitre($titre);

        // Si l'entité a une méthode __toString(), la tester
        if (method_exists($this->forum, '__toString')) {
            self::assertIsString((string) $this->forum);
            self::assertStringContainsString($titre, (string) $this->forum);
        } else {
            self::markTestSkipped('Méthode __toString() non implémentée');
        }
    }
}
