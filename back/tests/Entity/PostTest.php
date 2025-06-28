<?php

namespace App\Tests\Entity;

use App\Entity\Post;
use App\Entity\Message;
use App\Entity\Forum;
use PHPUnit\Framework\TestCase;

class PostTest extends TestCase
{
    private Post $post;

    protected function setUp(): void
    {
        $this->post = new Post();
    }

    public function testConstructor(): void
    {
        // Vérifier que les valeurs par défaut sont correctement définies
        self::assertInstanceOf(\DateTime::class, $this->post->getDateCreation());
        self::assertFalse($this->post->isVerrouille());
        self::assertEmpty($this->post->getMessages());
        self::assertNull($this->post->getId());
        self::assertNull($this->post->getTitre());
        self::assertNull($this->post->getVues());
        self::assertNull($this->post->isEpingle());
        self::assertNull($this->post->getForum());
    }

    public function testIdGetter(): void
    {
        // L'ID devrait être null avant la persistance
        self::assertNull($this->post->getId());
    }

    public function testTitreGetterAndSetter(): void
    {
        $titre = 'Titre du post de discussion';
        
        self::assertNull($this->post->getTitre());
        
        $result = $this->post->setTitre($titre);
        self::assertEquals($titre, $this->post->getTitre());
        self::assertInstanceOf(Post::class, $result); // Test fluent interface
    }

    public function testTitreLength(): void
    {
        $validTitre = 'Post Test'; // Moins de 30 caractères
        $longTitre = str_repeat('a', 35); // Plus de 30 caractères
        
        $this->post->setTitre($validTitre);
        self::assertEquals($validTitre, $this->post->getTitre());
        
        $this->post->setTitre($longTitre);
        self::assertEquals($longTitre, $this->post->getTitre());
    }

    public function testDateCreationGetter(): void
    {
        // La date de création est définie dans le constructeur
        self::assertInstanceOf(\DateTime::class, $this->post->getDateCreation());
        
        $now = new \DateTime();
        self::assertLessThanOrEqual($now, $this->post->getDateCreation());
    }

    public function testDateCreationGetterAndSetter(): void
    {
        // Note: Il y a une incohérence dans l'entité
        // La propriété est $dateCreation mais le getter est getDatCreation()
        // et le setter est setDatCreation()
        
        $dateCreation = new \DateTime('2024-01-01 10:00:00');
        
        $result = $this->post->setDatCreation($dateCreation);
        self::assertEquals($dateCreation, $this->post->getDatCreation());
        self::assertInstanceOf(Post::class, $result);
    }

    public function testVuesGetterAndSetter(): void
    {
        $vues = 42;
        
        self::assertNull($this->post->getVues());
        
        $result = $this->post->setVues($vues);
        self::assertEquals($vues, $this->post->getVues());
        self::assertInstanceOf(Post::class, $result);
    }

    public function testVuesValues(): void
    {
        // Test avec différentes valeurs de vues
        $vuesValues = [0, 1, 10, 100, 1000, 9999];
        
        foreach ($vuesValues as $vues) {
            $this->post->setVues($vues);
            self::assertEquals($vues, $this->post->getVues());
        }
    }

    public function testVerrouilleGetterAndSetter(): void
    {
        // Par défaut devrait être false
        self::assertFalse($this->post->isVerrouille());
        
        $result = $this->post->setVerrouille(true);
        self::assertTrue($this->post->isVerrouille());
        self::assertInstanceOf(Post::class, $result);
        
        $this->post->setVerrouille(false);
        self::assertFalse($this->post->isVerrouille());
        
        // Test avec null
        $this->post->setVerrouille(null);
        self::assertNull($this->post->isVerrouille());
    }

    public function testEpingleGetterAndSetter(): void
    {
        self::assertNull($this->post->isEpingle());
        
        $result = $this->post->setEpingle(true);
        self::assertTrue($this->post->isEpingle());
        self::assertInstanceOf(Post::class, $result);
        
        $this->post->setEpingle(false);
        self::assertFalse($this->post->isEpingle());
        
        // Test avec null
        $this->post->setEpingle(null);
        self::assertNull($this->post->isEpingle());
    }

    public function testMessagesCollection(): void
    {
        $message = $this->createMock(Message::class);
        $message->method('setPost')->willReturn($message);
        $message->method('getPost')->willReturn($this->post);
        
        // Tester l'ajout d'un message
        self::assertEmpty($this->post->getMessages());
        
        $result = $this->post->addMessage($message);
        self::assertCount(1, $this->post->getMessages());
        self::assertTrue($this->post->getMessages()->contains($message));
        self::assertInstanceOf(Post::class, $result);
        
        // Tester la suppression d'un message
        $this->post->removeMessage($message);
        self::assertEmpty($this->post->getMessages());
        self::assertFalse($this->post->getMessages()->contains($message));
    }

    public function testForumRelation(): void
    {
        $forum = $this->createMock(Forum::class);
        
        self::assertNull($this->post->getForum());
        
        $result = $this->post->setForum($forum);
        self::assertEquals($forum, $this->post->getForum());
        self::assertInstanceOf(Post::class, $result);
        
        // Test de suppression de la relation
        $this->post->setForum(null);
        self::assertNull($this->post->getForum());
    }

    public function testFluentInterface(): void
    {
        // Test que toutes les méthodes setter retournent l'instance
        $titre = 'Post de test';
        $vues = 10;
        $verrouille = true;
        $epingle = false;
        $dateCreation = new \DateTime();
        $forum = $this->createMock(Forum::class);
        
        $result = $this->post
            ->setTitre($titre)
            ->setVues($vues)
            ->setVerrouille($verrouille)
            ->setEpingle($epingle)
            ->setDatCreation($dateCreation)
            ->setForum($forum);
        
        self::assertInstanceOf(Post::class, $result);
        self::assertEquals($titre, $this->post->getTitre());
        self::assertEquals($vues, $this->post->getVues());
        self::assertTrue($this->post->isVerrouille());
        self::assertFalse($this->post->isEpingle());
        self::assertEquals($dateCreation, $this->post->getDatCreation());
        self::assertEquals($forum, $this->post->getForum());
    }

    public function testCompletePost(): void
    {
        // Test d'un post complet
        $titre = 'Discussion sur les nouvelles fonctionnalités';
        $vues = 150;
        $verrouille = false;
        $epingle = true;
        $dateCreation = new \DateTime('2024-01-01 10:00:00');
        $forum = $this->createMock(Forum::class);
        
        $this->post
            ->setTitre($titre)
            ->setVues($vues)
            ->setVerrouille($verrouille)
            ->setEpingle($epingle)
            ->setDatCreation($dateCreation)
            ->setForum($forum);
        
        // Vérifications
        self::assertEquals($titre, $this->post->getTitre());
        self::assertEquals($vues, $this->post->getVues());
        self::assertFalse($this->post->isVerrouille());
        self::assertTrue($this->post->isEpingle());
        self::assertEquals($dateCreation, $this->post->getDatCreation());
        self::assertEquals($forum, $this->post->getForum());
    }

    public function testMultipleMessages(): void
    {
        $message1 = $this->createMock(Message::class);
        $message1->method('setPost')->willReturn($message1);
        $message2 = $this->createMock(Message::class);
        $message2->method('setPost')->willReturn($message2);
        $message3 = $this->createMock(Message::class);
        $message3->method('setPost')->willReturn($message3);
        
        $this->post->addMessage($message1);
        $this->post->addMessage($message2);
        $this->post->addMessage($message3);
        
        self::assertCount(3, $this->post->getMessages());
        self::assertTrue($this->post->getMessages()->contains($message1));
        self::assertTrue($this->post->getMessages()->contains($message2));
        self::assertTrue($this->post->getMessages()->contains($message3));
        
        // Supprimer un message
        $this->post->removeMessage($message2);
        self::assertCount(2, $this->post->getMessages());
        self::assertFalse($this->post->getMessages()->contains($message2));
    }

    public function testPostStates(): void
    {
        // Test des différents états d'un post
        
        // Post normal
        $this->post->setVerrouille(false);
        $this->post->setEpingle(false);
        self::assertFalse($this->post->isVerrouille());
        self::assertFalse($this->post->isEpingle());
        
        // Post épinglé
        $this->post->setEpingle(true);
        self::assertTrue($this->post->isEpingle());
        self::assertFalse($this->post->isVerrouille());
        
        // Post verrouillé
        $this->post->setVerrouille(true);
        self::assertTrue($this->post->isVerrouille());
        self::assertTrue($this->post->isEpingle());
        
        // Post épinglé et verrouillé
        self::assertTrue($this->post->isEpingle());
        self::assertTrue($this->post->isVerrouille());
    }

    public function testVuesIncrement(): void
    {
        // Test d'incrémentation des vues
        $this->post->setVues(0);
        self::assertEquals(0, $this->post->getVues());
        
        // Simuler des vues
        for ($i = 1; $i <= 10; $i++) {
            $this->post->setVues($i);
            self::assertEquals($i, $this->post->getVues());
        }
    }

    public function testNegativeVues(): void
    {
        // Test avec des vues négatives (cas d'erreur)
        $this->post->setVues(-1);
        self::assertEquals(-1, $this->post->getVues());
        
        $this->post->setVues(-100);
        self::assertEquals(-100, $this->post->getVues());
    }

    public function testEmptyTitre(): void
    {
        // Test avec un titre vide
        $this->post->setTitre('');
        self::assertEquals('', $this->post->getTitre());
    }

    public function testSpecialCharactersInTitre(): void
    {
        // Test avec des caractères spéciaux dans le titre
        $specialTitres = [
            'Titre avec accents: àéèùç',
            'Titre avec symbols: @#$%^&*()',
            'Titre avec quotes: "test" et \'test\'',
            'Titre avec emoji: 🚀 Discussion',
            'Titre avec HTML: <b>Important</b>',
            'Titre avec & esperluette'
        ];
        
        foreach ($specialTitres as $titre) {
            $this->post->setTitre($titre);
            self::assertEquals($titre, $this->post->getTitre());
        }
    }

    public function testPostLifecycle(): void
    {
        // Test du cycle de vie d'un post
        $titre = 'Nouveau sujet de discussion';
        $forum = $this->createMock(Forum::class);
        
        // 1. Création
        $this->post
            ->setTitre($titre)
            ->setVues(0)
            ->setForum($forum);
        
        self::assertEquals($titre, $this->post->getTitre());
        self::assertEquals(0, $this->post->getVues());
        self::assertFalse($this->post->isVerrouille());
        self::assertNull($this->post->isEpingle());
        
        // 2. Activation/Épinglage
        $this->post->setEpingle(true);
        self::assertTrue($this->post->isEpingle());
        
        // 3. Accumulation de vues
        $this->post->setVues(100);
        self::assertEquals(100, $this->post->getVues());
        
        // 4. Verrouillage
        $this->post->setVerrouille(true);
        self::assertTrue($this->post->isVerrouille());
        
        // 5. Désépinglage
        $this->post->setEpingle(false);
        self::assertFalse($this->post->isEpingle());
        
        // État final: verrouillé mais pas épinglé
        self::assertTrue($this->post->isVerrouille());
        self::assertFalse($this->post->isEpingle());
    }

    public function testPostTypes(): void
    {
        // Test de différents types de posts
        $postTypes = [
            ['Annonce importante', true, true],   // Épinglé et verrouillé
            ['Discussion ouverte', false, false], // Normal
            ['Sujet archivé', true, false],       // Verrouillé seulement
            ['Info utile', false, true],          // Épinglé seulement
        ];
        
        foreach ($postTypes as [$titre, $verrouille, $epingle]) {
            $post = new Post();
            $post
                ->setTitre($titre)
                ->setVerrouille($verrouille)
                ->setEpingle($epingle);
            
            self::assertEquals($titre, $post->getTitre());
            self::assertEquals($verrouille, $post->isVerrouille());
            self::assertEquals($epingle, $post->isEpingle());
        }
    }

    public function testPostWithoutForum(): void
    {
        // Test d'un post sans forum (cas d'erreur ou brouillon)
        $this->post->setTitre('Post orphelin');
        
        self::assertEquals('Post orphelin', $this->post->getTitre());
        self::assertNull($this->post->getForum());
    }

    public function testDateCreationConsistency(): void
    {
        // Vérifier que la date de création est cohérente
        $beforeCreation = new \DateTime();
        $post = new Post();
        $afterCreation = new \DateTime();
        
        self::assertGreaterThanOrEqual($beforeCreation, $post->getDateCreation());
        self::assertLessThanOrEqual($afterCreation, $post->getDateCreation());
    }

    public function testPropertyNameInconsistency(): void
    {
        // Test pour documenter l'incohérence dans les noms
        // Propriété: $dateCreation
        // Getter: getDatCreation() (manque le 'e')
        // Setter: setDatCreation() (manque le 'e')
        
        $date = new \DateTime('2024-01-01');
        $this->post->setDatCreation($date);
        
        self::assertEquals($date, $this->post->getDatCreation());
        
        // Note: $this->post->getDateCreation() retourne la valeur du constructeur
        self::assertNotEquals($date, $this->post->getDateCreation());
    }

    public function testToString(): void
    {
        $titre = 'Test Post';
        $this->post->setTitre($titre);
        
        // Si l'entité a une méthode __toString(), la tester
        if (method_exists($this->post, '__toString')) {
            self::assertIsString((string) $this->post);
            self::assertStringContainsString($titre, (string) $this->post);
        } else {
            self::markTestSkipped('Méthode __toString() non implémentée');
        }
    }
}
