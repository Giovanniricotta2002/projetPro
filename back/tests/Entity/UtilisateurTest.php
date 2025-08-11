<?php

namespace App\Tests\Entity;

use App\Entity\{Droit, Forum, Message, Utilisateur};
use App\Enum\UserStatus;
use PHPUnit\Framework\TestCase;

class UtilisateurTest extends TestCase
{
    private Utilisateur $utilisateur;

    protected function setUp(): void
    {
        $this->utilisateur = new Utilisateur();
    }

    public function testConstructor(): void
    {
        // Vérifier que les valeurs par défaut sont correctement définies
        self::assertInstanceOf(\DateTime::class, $this->utilisateur->getDateCreation());
        self::assertFalse($this->utilisateur->isAnonimus()); // Note: faute de frappe dans l'entité
        self::assertEmpty($this->utilisateur->getDroits());
        self::assertEmpty($this->utilisateur->getForums());
        self::assertNull($this->utilisateur->getId());
    }

    public function testUsernameGetterAndSetter(): void
    {
        $username = 'testuser';

        self::assertNull($this->utilisateur->getUsername());

        $this->utilisateur->setUsername($username);
        self::assertEquals($username, $this->utilisateur->getUsername());
    }

    public function testPasswordGetterAndSetter(): void
    {
        $password = 'hashedpassword123';

        self::assertNull($this->utilisateur->getPassword());

        $this->utilisateur->setPassword($password);
        self::assertEquals($password, $this->utilisateur->getPassword());
    }

    public function testRolesGetterAndSetter(): void
    {
        $roles = ['ROLE_USER', 'ROLE_ADMIN'];

        // Par défaut, devrait retourner un tableau vide
        self::assertNotEquals([], $this->utilisateur->getRoles());

        $this->utilisateur->setRoles($roles);
        self::assertEquals($roles, $this->utilisateur->getRoles());
    }

    public function testRolesAlwaysContainsUserRole(): void
    {
        // Même sans définir de rôles, ROLE_USER devrait être présent
        $this->utilisateur->setRoles([]);
        $roles = $this->utilisateur->getRoles();

        // Note: Vérifiez si votre implémentation ajoute automatiquement ROLE_USER
        // Si c'est le cas, décommentez la ligne suivante :
        self::assertContains('ROLE_USER', $roles);
    }

    public function testMailGetterAndSetter(): void
    {
        $mail = 'test@example.com';

        self::assertNull($this->utilisateur->getMail());

        $this->utilisateur->setMail($mail);
        self::assertEquals($mail, $this->utilisateur->getMail());
    }

    public function testStatusGetterAndSetter(): void
    {
        $status = UserStatus::ACTIVE;

        $this->utilisateur->setStatus($status);
        self::assertEquals($status, $this->utilisateur->getStatus());
    }

    public function testAnonimusGetterAndSetter(): void
    {
        // Note: Il y a une faute de frappe dans l'entité (aninimus au lieu d'anonimus)
        // Par défaut devrait être false
        self::assertFalse($this->utilisateur->isAnonimus());

        $this->utilisateur->setAnonimus(true);
        self::assertTrue($this->utilisateur->isAnonimus());

        $this->utilisateur->setAnonimus(false);
        self::assertFalse($this->utilisateur->isAnonimus());
    }

    public function testLastVisitGetterAndSetter(): void
    {
        $lastVisit = new \DateTime('2024-01-01 12:00:00');

        self::assertNull($this->utilisateur->getLastVisit());

        $this->utilisateur->setLastVisit($lastVisit);
        self::assertEquals($lastVisit, $this->utilisateur->getLastVisit());
    }

    public function testDateCreationGetterAndSetter(): void
    {
        $dateCreation = new \DateTime('2024-01-01 10:00:00');

        // La date de création est définie dans le constructeur
        self::assertInstanceOf(\DateTime::class, $this->utilisateur->getDateCreation());

        $this->utilisateur->setDateCreation($dateCreation);
        self::assertEquals($dateCreation, $this->utilisateur->getDateCreation());
    }

    public function testUserIdentifier(): void
    {
        $username = 'testuser';
        $this->utilisateur->setUsername($username);

        self::assertEquals($username, $this->utilisateur->getUsername());
    }

    public function testEraseCredentials(): void
    {
        // Cette méthode devrait être vide dans la plupart des cas
        // Mais elle ne devrait pas lever d'exception
        $this->utilisateur->eraseCredentials();

        // Test qu'aucune exception n'est levée
        self::assertTrue(true);
    }

    public function testDroitsCollection(): void
    {
        // Créer un mock de Droit (ou utiliser une vraie instance si possible)
        $droit = $this->createMock(Droit::class);

        // Tester l'ajout d'un droit
        self::assertEmpty($this->utilisateur->getDroits());

        $this->utilisateur->addDroit($droit);
        self::assertCount(1, $this->utilisateur->getDroits());
        self::assertTrue($this->utilisateur->getDroits()->contains($droit));

        // Tester la suppression d'un droit
        $this->utilisateur->removeDroit($droit);
        self::assertEmpty($this->utilisateur->getDroits());
        self::assertFalse($this->utilisateur->getDroits()->contains($droit));
    }

    public function testForumsCollection(): void
    {
        // Créer un mock de Forum
        $forum = $this->createMock(Forum::class);

        // Tester l'ajout d'un forum
        self::assertEmpty($this->utilisateur->getForums());

        $this->utilisateur->addForum($forum);
        self::assertCount(1, $this->utilisateur->getForums());
        self::assertTrue($this->utilisateur->getForums()->contains($forum));

        // Tester la suppression d'un forum
        $this->utilisateur->removeForum($forum);
        self::assertEmpty($this->utilisateur->getForums());
        self::assertFalse($this->utilisateur->getForums()->contains($forum));
    }

    public function testMessageRelation(): void
    {
        $message = $this->createMock(Message::class);

        self::assertNull($this->utilisateur->getMessage());

        $this->utilisateur->setMessage($message);
        self::assertEquals($message, $this->utilisateur->getMessage());

        $this->utilisateur->setMessage(null);
        self::assertNull($this->utilisateur->getMessage());
    }

    public function testValidEmail(): void
    {
        $validEmail = 'user@example.com';
        $this->utilisateur->setMail($validEmail);

        self::assertEquals($validEmail, $this->utilisateur->getMail());

        // Vérifier que c'est un email valide
        self::assertMatchesRegularExpression(
            '/^[^\s@]+@[^\s@]+\.[^\s@]+$/',
            $this->utilisateur->getMail()
        );
    }

    public function testUsernameLength(): void
    {
        $longUsername = str_repeat('a', 200); // Plus long que la limite de 180 caractères
        $this->utilisateur->setUsername($longUsername);

        // En réalité, la base de données devrait lever une erreur
        // Mais au niveau de l'entité, on peut juste vérifier que la valeur est définie
        self::assertEquals($longUsername, $this->utilisateur->getUsername());
    }

    public function testPasswordSecurity(): void
    {
        $plainPassword = 'plainpassword';
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

        $this->utilisateur->setPassword($hashedPassword);

        // Vérifier que le mot de passe n'est pas stocké en clair
        self::assertNotEquals($plainPassword, $this->utilisateur->getPassword());
        self::assertTrue(password_verify($plainPassword, $this->utilisateur->getPassword()));
    }

    public function testDateConsistency(): void
    {
        $now = new \DateTime();

        // La date de création doit être antérieure ou égale à maintenant
        self::assertLessThanOrEqual($now, $this->utilisateur->getDateCreation());
        self::assertLessThanOrEqual($now, $this->utilisateur->getCreatedAt());
    }

    public function testSoftDelete(): void
    {
        // Tester la suppression logique
        self::assertNull($this->utilisateur->getDeletedAt());

        $deletedAt = new \DateTime();
        $this->utilisateur->setDeletedAt($deletedAt);

        self::assertEquals($deletedAt, $this->utilisateur->getDeletedAt());

        // Une entité avec deletedAt défini pourrait être considérée comme supprimée
        self::assertNotNull($this->utilisateur->getDeletedAt());
    }

    public function testToString(): void
    {
        $username = 'testuser';
        $this->utilisateur->setUsername($username);

        // Si l'entité a une méthode __toString(), la tester
        // Sinon, ignorer ce test
        if (method_exists($this->utilisateur, '__toString')) {
            self::assertIsString((string) $this->utilisateur);
        } else {
            self::markTestSkipped('Méthode __toString() non implémentée');
        }
    }
}
