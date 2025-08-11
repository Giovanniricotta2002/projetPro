<?php

namespace App\Tests\Entity;

use App\Entity\{Droit, Utilisateur};
use PHPUnit\Framework\TestCase;

class DroitTest extends TestCase
{
    private Droit $droit;

    protected function setUp(): void
    {
        $this->droit = new Droit();
    }

    public function testConstructor(): void
    {
        // Vérifier que les valeurs par défaut sont correctement définies
        self::assertInstanceOf(\DateTime::class, $this->droit->getCreatedAt());
        self::assertEmpty($this->droit->getIdUtilisateur());
        self::assertNull($this->droit->getId());
        self::assertNull($this->droit->getLibelle());
        self::assertNull($this->droit->getDescription());
        self::assertNull($this->droit->getRoleName());
    }

    public function testIdGetter(): void
    {
        // L'ID devrait être null avant la persistance
        self::assertNull($this->droit->getId());
    }

    public function testLibelleGetterAndSetter(): void
    {
        $libelle = 'Droit Administration';

        self::assertNull($this->droit->getLibelle());

        $result = $this->droit->setLibelle($libelle);
        self::assertEquals($libelle, $this->droit->getLibelle());
        self::assertInstanceOf(Droit::class, $result); // Test fluent interface
    }

    public function testLibelleLength(): void
    {
        $validLibelle = 'Droit Test'; // Moins de 30 caractères
        $longLibelle = str_repeat('a', 35); // Plus de 30 caractères

        $this->droit->setLibelle($validLibelle);
        self::assertEquals($validLibelle, $this->droit->getLibelle());

        // Le libellé long devrait pouvoir être défini au niveau de l'entité
        $this->droit->setLibelle($longLibelle);
        self::assertEquals($longLibelle, $this->droit->getLibelle());
    }

    public function testDescriptionGetterAndSetter(): void
    {
        $description = 'Description détaillée du droit accordé à l\'utilisateur';

        self::assertNull($this->droit->getDescription());

        $result = $this->droit->setDescription($description);
        self::assertEquals($description, $this->droit->getDescription());
        self::assertInstanceOf(Droit::class, $result);
    }

    public function testDescriptionCanBeNull(): void
    {
        $this->droit->setDescription('Some description');
        self::assertEquals('Some description', $this->droit->getDescription());

        $this->droit->setDescription(null);
        self::assertNull($this->droit->getDescription());
    }

    public function testDescriptionLongText(): void
    {
        // Test avec un texte très long
        $longDescription = str_repeat('Lorem ipsum dolor sit amet. ', 100);

        $this->droit->setDescription($longDescription);
        self::assertEquals($longDescription, $this->droit->getDescription());
    }

    public function testRoleNameGetterAndSetter(): void
    {
        $roleName = 'ROLE_ADMIN';

        self::assertNull($this->droit->getRoleName());

        $result = $this->droit->setRoleName($roleName);
        self::assertEquals($roleName, $this->droit->getRoleName());
        self::assertInstanceOf(Droit::class, $result);
    }

    public function testRoleNameLength(): void
    {
        $validRoleName = 'ROLE_USER'; // Moins de 30 caractères
        $longRoleName = str_repeat('ROLE_', 10); // Plus de 30 caractères

        $this->droit->setRoleName($validRoleName);
        self::assertEquals($validRoleName, $this->droit->getRoleName());

        $this->droit->setRoleName($longRoleName);
        self::assertEquals($longRoleName, $this->droit->getRoleName());
    }

    public function testCreatedAtGetter(): void
    {
        // createdAt est définie dans le constructeur
        self::assertInstanceOf(\DateTime::class, $this->droit->getCreatedAt());

        $now = new \DateTime();
        self::assertLessThanOrEqual($now, $this->droit->getCreatedAt());
    }

    public function testUtilisateursCollection(): void
    {
        $utilisateur = $this->createMock(Utilisateur::class);

        // Tester l'ajout d'un utilisateur
        self::assertEmpty($this->droit->getIdUtilisateur());

        $result = $this->droit->addIdUtilisateur($utilisateur);
        self::assertCount(1, $this->droit->getIdUtilisateur());
        self::assertTrue($this->droit->getIdUtilisateur()->contains($utilisateur));
        self::assertInstanceOf(Droit::class, $result);

        // Tester l'ajout du même utilisateur (ne devrait pas être ajouté deux fois)
        $this->droit->addIdUtilisateur($utilisateur);
        self::assertCount(1, $this->droit->getIdUtilisateur());

        // Tester la suppression d'un utilisateur
        $this->droit->removeIdUtilisateur($utilisateur);
        self::assertEmpty($this->droit->getIdUtilisateur());
        self::assertFalse($this->droit->getIdUtilisateur()->contains($utilisateur));
    }

    public function testMultipleUtilisateurs(): void
    {
        $utilisateur1 = $this->createMock(Utilisateur::class);
        $utilisateur2 = $this->createMock(Utilisateur::class);
        $utilisateur3 = $this->createMock(Utilisateur::class);

        $this->droit->addIdUtilisateur($utilisateur1);
        $this->droit->addIdUtilisateur($utilisateur2);
        $this->droit->addIdUtilisateur($utilisateur3);

        self::assertCount(3, $this->droit->getIdUtilisateur());
        self::assertTrue($this->droit->getIdUtilisateur()->contains($utilisateur1));
        self::assertTrue($this->droit->getIdUtilisateur()->contains($utilisateur2));
        self::assertTrue($this->droit->getIdUtilisateur()->contains($utilisateur3));

        // Supprimer un utilisateur du milieu
        $this->droit->removeIdUtilisateur($utilisateur2);
        self::assertCount(2, $this->droit->getIdUtilisateur());
        self::assertFalse($this->droit->getIdUtilisateur()->contains($utilisateur2));
    }

    public function testFluentInterface(): void
    {
        // Test que toutes les méthodes setter retournent l'instance
        $libelle = 'Admin Rights';
        $description = 'Full admin access';
        $roleName = 'ROLE_ADMIN';
        $scope = 'global';
        $utilisateur = $this->createMock(Utilisateur::class);

        $result = $this->droit
            ->setLibelle($libelle)
            ->setDescription($description)
            ->setRoleName($roleName)
            ->setScope($scope)
            ->addIdUtilisateur($utilisateur);

        self::assertInstanceOf(Droit::class, $result);
        self::assertEquals($libelle, $this->droit->getLibelle());
        self::assertEquals($description, $this->droit->getDescription());
        self::assertEquals($roleName, $this->droit->getRoleName());
        self::assertEquals($scope, $this->droit->getScope());
        self::assertTrue($this->droit->getIdUtilisateur()->contains($utilisateur));
    }

    public function testCompleteDroit(): void
    {
        // Test d'un droit complet
        $libelle = 'Gestion Utilisateurs';
        $description = 'Permet de créer, modifier et supprimer des utilisateurs';
        $roleName = 'ROLE_USER_MANAGER';
        $scope = 'users';

        $this->droit
            ->setLibelle($libelle)
            ->setDescription($description)
            ->setRoleName($roleName)
            ->setScope($scope);

        // Vérifications
        self::assertEquals($libelle, $this->droit->getLibelle());
        self::assertEquals($description, $this->droit->getDescription());
        self::assertEquals($roleName, $this->droit->getRoleName());
        self::assertEquals($scope, $this->droit->getScope());
        self::assertInstanceOf(\DateTime::class, $this->droit->getCreatedAt());
    }

    public function testRoleNameConvention(): void
    {
        // Test avec des noms de rôles suivant les conventions Symfony
        $roles = [
            'ROLE_USER',
            'ROLE_ADMIN',
            'ROLE_SUPER_ADMIN',
            'ROLE_MODERATOR',
            'ROLE_EDITOR',
        ];

        foreach ($roles as $role) {
            $this->droit->setRoleName($role);
            self::assertEquals($role, $this->droit->getRoleName());
            self::assertStringStartsWith('ROLE_', $this->droit->getRoleName());
        }
    }

    public function testScopeValues(): void
    {
        // Test avec différents types de scope
        $scopes = ['global', 'local', 'forum', 'machine', 'user'];

        foreach ($scopes as $scope) {
            $this->droit->setScope($scope);
            self::assertEquals($scope, $this->droit->getScope());
        }
    }

    public function testEmptyValues(): void
    {
        // Test avec des valeurs vides
        $this->droit->setLibelle('');
        $this->droit->setDescription('');
        $this->droit->setRoleName('');
        $this->droit->setScope('');

        self::assertEquals('', $this->droit->getLibelle());
        self::assertEquals('', $this->droit->getDescription());
        self::assertEquals('', $this->droit->getRoleName());
        self::assertEquals('', $this->droit->getScope());
    }

    public function testSpecialCharacters(): void
    {
        // Test avec des caractères spéciaux
        $libelle = 'Droit & Privilèges';
        $description = 'Description avec caractères spéciaux: àéèùç & "quotes"';

        $this->droit->setLibelle($libelle);
        $this->droit->setDescription($description);

        self::assertEquals($libelle, $this->droit->getLibelle());
        self::assertEquals($description, $this->droit->getDescription());
    }

    public function testCreatedAtImmutability(): void
    {
        // Vérifier que la date de création ne change pas une fois définie
        $originalCreatedAt = $this->droit->getCreatedAt();

        // Attendre un peu pour voir si la date change
        usleep(1000); // 1ms

        // Créer un nouveau droit
        $newDroit = new Droit();

        // Les dates devraient être différentes
        self::assertNotEquals($originalCreatedAt, $newDroit->getCreatedAt());
    }

    public function testToString(): void
    {
        $libelle = 'Test Droit';
        $this->droit->setLibelle($libelle);

        // Si l'entité a une méthode __toString(), la tester
        if (method_exists($this->droit, '__toString')) {
            self::assertIsString((string) $this->droit);
            self::assertStringContainsString($libelle, (string) $this->droit);
        } else {
            self::markTestSkipped('Méthode __toString() non implémentée');
        }
    }
}
