<?php

namespace App\Tests\Entity;

use App\Entity\Message;
use App\Entity\Utilisateur;
use App\Entity\Post;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    private Message $message;

    protected function setUp(): void
    {
        $this->message = new Message();
    }

    public function testConstructor(): void
    {
        // Vérifier que les valeurs par défaut sont correctement définies
        self::assertInstanceOf(\DateTime::class, $this->message->getDateCreation());
        self::assertTrue($this->message->isVisible());
        self::assertNull($this->message->getId());
        self::assertNull($this->message->getText());
        self::assertNull($this->message->getDateModification());
        self::assertNull($this->message->getDateSuppresion());
        self::assertNull($this->message->getUtilisateur());
        self::assertNull($this->message->getPost());
    }

    public function testIdGetter(): void
    {
        // L'ID devrait être null avant la persistance
        self::assertNull($this->message->getId());
    }

    public function testTextGetterAndSetter(): void
    {
        $text = 'Ceci est un message de test';
        
        self::assertNull($this->message->getText());
        
        $result = $this->message->setText($text);
        self::assertEquals($text, $this->message->getText());
        self::assertInstanceOf(Message::class, $result); // Test fluent interface
    }

    public function testTextLongContent(): void
    {
        // Test avec un texte très long (TEXT type)
        $longText = str_repeat('Lorem ipsum dolor sit amet, consectetur adipiscing elit. ', 300);
        
        $this->message->setText($longText);
        self::assertEquals($longText, $this->message->getText());
        self::assertGreaterThan(1000, strlen($this->message->getText()));
    }

    public function testTextWithSpecialCharacters(): void
    {
        // Test avec des caractères spéciaux et HTML
        $specialText = 'Message avec caractères spéciaux: àéèùç & "quotes" <strong>bold</strong>';
        
        $this->message->setText($specialText);
        self::assertEquals($specialText, $this->message->getText());
    }

    public function testTextWithLineBreaks(): void
    {
        // Test avec des retours à la ligne
        $textWithBreaks = "Ligne 1\nLigne 2\r\nLigne 3\n\nLigne 5";
        
        $this->message->setText($textWithBreaks);
        self::assertEquals($textWithBreaks, $this->message->getText());
    }

    public function testDateCreationGetter(): void
    {
        // La date de création est définie dans le constructeur
        self::assertInstanceOf(\DateTime::class, $this->message->getDateCreation());
        
        $now = new \DateTime();
        self::assertLessThanOrEqual($now, $this->message->getDateCreation());
    }

    public function testDateCreationGetterAndSetter(): void
    {
        $dateCreation = new \DateTime('2024-01-01 10:00:00');
        
        $result = $this->message->setDateCreation($dateCreation);
        self::assertEquals($dateCreation, $this->message->getDateCreation());
        self::assertInstanceOf(Message::class, $result);
    }

    public function testDateModificationGetterAndSetter(): void
    {
        $dateModification = new \DateTime('2024-01-02 15:30:00');
        
        self::assertNull($this->message->getDateModification());
        
        $result = $this->message->setDateModification($dateModification);
        self::assertEquals($dateModification, $this->message->getDateModification());
        self::assertInstanceOf(Message::class, $result);
        
        // Test avec null
        $this->message->setDateModification(null);
        self::assertNull($this->message->getDateModification());
    }

    public function testDateSuppressionGetterAndSetter(): void
    {
        $dateSuppression = new \DateTime('2024-01-03 10:00:00');
        
        self::assertNull($this->message->getDateSuppresion());
        
        $result = $this->message->setDateSuppresion($dateSuppression);
        self::assertEquals($dateSuppression, $this->message->getDateSuppresion());
        self::assertInstanceOf(Message::class, $result);
        
        // Test avec null
        $this->message->setDateSuppresion(null);
        self::assertNull($this->message->getDateSuppresion());
    }

    public function testVisibleGetterAndSetter(): void
    {
        // Par défaut devrait être true
        self::assertTrue($this->message->isVisible());
        
        $result = $this->message->setVisible(false);
        self::assertFalse($this->message->isVisible());
        self::assertInstanceOf(Message::class, $result);
        
        $this->message->setVisible(true);
        self::assertTrue($this->message->isVisible());
    }

    public function testUtilisateurRelation(): void
    {
        $utilisateur = $this->createMock(Utilisateur::class);
        
        self::assertNull($this->message->getUtilisateur());
        
        $result = $this->message->setUtilisateur($utilisateur);
        self::assertEquals($utilisateur, $this->message->getUtilisateur());
        self::assertInstanceOf(Message::class, $result);
        
        // Test de suppression de la relation
        $this->message->setUtilisateur(null);
        self::assertNull($this->message->getUtilisateur());
    }

    public function testPostRelation(): void
    {
        $post = $this->createMock(Post::class);
        
        self::assertNull($this->message->getPost());
        
        $result = $this->message->setPost($post);
        self::assertEquals($post, $this->message->getPost());
        self::assertInstanceOf(Message::class, $result);
        
        // Test de suppression de la relation
        $this->message->setPost(null);
        self::assertNull($this->message->getPost());
    }

    public function testFluentInterface(): void
    {
        // Test que toutes les méthodes setter retournent l'instance
        $text = 'Message de test';
        $dateCreation = new \DateTime();
        $dateModification = new \DateTime();
        $visible = false;
        $utilisateur = $this->createMock(Utilisateur::class);
        $post = $this->createMock(Post::class);
        
        $result = $this->message
            ->setText($text)
            ->setDateCreation($dateCreation)
            ->setDateModification($dateModification)
            ->setVisible($visible)
            ->setUtilisateur($utilisateur)
            ->setPost($post);
        
        self::assertInstanceOf(Message::class, $result);
        self::assertEquals($text, $this->message->getText());
        self::assertEquals($dateCreation, $this->message->getDateCreation());
        self::assertEquals($dateModification, $this->message->getDateModification());
        self::assertFalse($this->message->isVisible());
        self::assertEquals($utilisateur, $this->message->getUtilisateur());
        self::assertEquals($post, $this->message->getPost());
    }

    public function testCompleteMessage(): void
    {
        // Test d'un message complet
        $text = 'Ceci est un message complet avec du contenu détaillé';
        $dateCreation = new \DateTime('2024-01-01 10:00:00');
        $dateModification = new \DateTime('2024-01-01 11:00:00');
        $visible = true;
        $utilisateur = $this->createMock(Utilisateur::class);
        $post = $this->createMock(Post::class);
        
        $this->message
            ->setText($text)
            ->setDateCreation($dateCreation)
            ->setDateModification($dateModification)
            ->setVisible($visible)
            ->setUtilisateur($utilisateur)
            ->setPost($post);
        
        // Vérifications
        self::assertEquals($text, $this->message->getText());
        self::assertEquals($dateCreation, $this->message->getDateCreation());
        self::assertEquals($dateModification, $this->message->getDateModification());
        self::assertTrue($this->message->isVisible());
        self::assertEquals($utilisateur, $this->message->getUtilisateur());
        self::assertEquals($post, $this->message->getPost());
    }

    public function testDateConsistency(): void
    {
        $dateCreation = new \DateTime('2024-01-01 10:00:00');
        $dateModification = new \DateTime('2024-01-01 11:00:00');
        $dateSuppression = new \DateTime('2024-01-01 12:00:00');
        
        $this->message->setDateCreation($dateCreation);
        $this->message->setDateModification($dateModification);
        $this->message->setDateSuppresion($dateSuppression);
        
        // Les dates devraient être dans l'ordre logique
        self::assertLessThan($dateModification, $dateCreation);
        self::assertLessThan($dateSuppression, $dateModification);
        self::assertLessThan($dateSuppression, $dateCreation);
    }

    public function testSoftDelete(): void
    {
        // Test de suppression logique
        self::assertNull($this->message->getDateSuppresion());
        self::assertTrue($this->message->isVisible());
        
        // Marquer comme supprimé
        $dateSuppression = new \DateTime();
        $this->message->setDateSuppresion($dateSuppression);
        $this->message->setVisible(false);
        
        self::assertEquals($dateSuppression, $this->message->getDateSuppresion());
        self::assertFalse($this->message->isVisible());
    }

    public function testMessageLifecycle(): void
    {
        // Test du cycle de vie d'un message
        $text = 'Message initial';
        $utilisateur = $this->createMock(Utilisateur::class);
        $post = $this->createMock(Post::class);
        
        // 1. Création
        $this->message
            ->setText($text)
            ->setUtilisateur($utilisateur)
            ->setPost($post);
        
        $creationDate = $this->message->getDateCreation();
        self::assertEquals($text, $this->message->getText());
        self::assertTrue($this->message->isVisible());
        self::assertNull($this->message->getDateModification());
        
        // 2. Modification
        $newText = 'Message modifié';
        $modificationDate = new \DateTime();
        $this->message
            ->setText($newText)
            ->setDateModification($modificationDate);
        
        self::assertEquals($newText, $this->message->getText());
        self::assertEquals($modificationDate, $this->message->getDateModification());
        self::assertTrue($this->message->isVisible());
        
        // 3. Suppression logique
        $suppressionDate = new \DateTime();
        $this->message
            ->setDateSuppresion($suppressionDate)
            ->setVisible(false);
        
        self::assertEquals($suppressionDate, $this->message->getDateSuppresion());
        self::assertFalse($this->message->isVisible());
        
        // Vérifier l'ordre des dates
        self::assertLessThan($modificationDate, $creationDate);
        self::assertLessThan($suppressionDate, $modificationDate);
    }

    public function testEmptyText(): void
    {
        // Test avec un texte vide
        $this->message->setText('');
        self::assertEquals('', $this->message->getText());
    }

    public function testHtmlContent(): void
    {
        // Test avec du contenu HTML
        $htmlContent = '<p>Paragraphe avec <strong>texte en gras</strong> et <em>italique</em></p>';
        
        $this->message->setText($htmlContent);
        self::assertEquals($htmlContent, $this->message->getText());
    }

    public function testMarkdownContent(): void
    {
        // Test avec du contenu Markdown
        $markdownContent = "# Titre\n\n## Sous-titre\n\n- Item 1\n- Item 2\n\n**Gras** et *italique*";
        
        $this->message->setText($markdownContent);
        self::assertEquals($markdownContent, $this->message->getText());
    }

    public function testUnicodeContent(): void
    {
        // Test avec du contenu Unicode
        $unicodeContent = 'Emoji: 😀😃😄 | Chinois: 你好 | Arabe: مرحبا | Russe: Привет';
        
        $this->message->setText($unicodeContent);
        self::assertEquals($unicodeContent, $this->message->getText());
    }

    public function testSecurityContent(): void
    {
        // Test avec du contenu potentiellement malicieux
        $maliciousContent = '<script>alert("XSS")</script>\'; DROP TABLE messages; --';
        
        $this->message->setText($maliciousContent);
        self::assertEquals($maliciousContent, $this->message->getText());
    }

    public function testVisibilityToggle(): void
    {
        // Test du basculement de visibilité
        self::assertTrue($this->message->isVisible());
        
        // Masquer
        $this->message->setVisible(false);
        self::assertFalse($this->message->isVisible());
        
        // Réafficher
        $this->message->setVisible(true);
        self::assertTrue($this->message->isVisible());
    }

    public function testMessageWithoutModification(): void
    {
        // Test d'un message jamais modifié
        $text = 'Message non modifié';
        
        $this->message->setText($text);
        
        self::assertEquals($text, $this->message->getText());
        self::assertNull($this->message->getDateModification());
        self::assertTrue($this->message->isVisible());
    }

    public function testMessageWithMultipleModifications(): void
    {
        // Test d'un message modifié plusieurs fois
        $originalText = 'Texte original';
        $firstModification = 'Première modification';
        $secondModification = 'Deuxième modification';
        
        // Création
        $this->message->setText($originalText);
        
        // Première modification
        $firstDate = new \DateTime('2024-01-01 11:00:00');
        $this->message
            ->setText($firstModification)
            ->setDateModification($firstDate);
        
        self::assertEquals($firstModification, $this->message->getText());
        self::assertEquals($firstDate, $this->message->getDateModification());
        
        // Deuxième modification
        $secondDate = new \DateTime('2024-01-01 12:00:00');
        $this->message
            ->setText($secondModification)
            ->setDateModification($secondDate);
        
        self::assertEquals($secondModification, $this->message->getText());
        self::assertEquals($secondDate, $this->message->getDateModification());
        
        // La date de modification devrait être la plus récente
        self::assertGreaterThan($firstDate, $secondDate);
    }

    public function testToString(): void
    {
        $text = 'Message de test';
        $this->message->setText($text);
        
        // Si l'entité a une méthode __toString(), la tester
        if (method_exists($this->message, '__toString')) {
            self::assertIsString((string) $this->message);
            self::assertStringContainsString($text, (string) $this->message);
        } else {
            self::markTestSkipped('Méthode __toString() non implémentée');
        }
    }
}
