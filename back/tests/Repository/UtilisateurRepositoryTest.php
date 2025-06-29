<?php

namespace App\Tests\Repository;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class UtilisateurRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private UtilisateurRepository $repository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->repository = $this->entityManager->getRepository(Utilisateur::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }

    public function testRepository(): void
    {
        // Test que le repository est bien une instance d'UtilisateurRepository
        self::assertInstanceOf(UtilisateurRepository::class, $this->repository);
    }

    public function testUpgradePasswordWithValidUser(): void
    {
        // Créer un utilisateur de test
        $user = new Utilisateur();
        $user->setUsername('testuser');
        $user->setPassword('oldpassword');

        $newHashedPassword = 'newhashedpassword123';

        // Tester la mise à jour du mot de passe
        $this->repository->upgradePassword($user, $newHashedPassword);

        // Vérifier que le mot de passe a été mis à jour
        self::assertEquals($newHashedPassword, $user->getPassword());
    }

    public function testUpgradePasswordWithInvalidUser(): void
    {
        // Créer un mock d'une autre classe implémentant PasswordAuthenticatedUserInterface
        $invalidUser = $this->createMock(\Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface::class);

        // Attendre une exception UnsupportedUserException
        $this->expectException(UnsupportedUserException::class);
        $this->expectExceptionMessage('Instances of');

        $this->repository->upgradePassword($invalidUser, 'newpassword');
    }

    public function testFindByUsernameIntegration(): void
    {
        // Test d'intégration - nécessite une base de données de test configurée
        // Ce test ne s'exécutera que si vous avez configuré une base de données de test

        // Créer et persister un utilisateur de test
        $user = new Utilisateur();
        $user->setUsername('integration_test_user');
        $user->setPassword('hashedpassword');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Tester la recherche par nom d'utilisateur
        $foundUser = $this->repository->findOneBy(['username' => 'integration_test_user']);

        self::assertNotNull($foundUser);
        self::assertEquals('integration_test_user', $foundUser->getUsername());

        // Nettoyer
        $this->entityManager->remove($foundUser);
        $this->entityManager->flush();
    }

    public function testFindActiveUsers(): void
    {
        // Exemple de test pour une méthode personnalisée (à implémenter dans le repository)
        // Cette méthode pourrait rechercher les utilisateurs actifs

        $this->markTestSkipped('Méthode findActiveUsers() pas encore implémentée dans le repository');

        /*
        // Si vous ajoutez cette méthode au repository :
        $activeUsers = $this->repository->findActiveUsers();
        self::assertIsArray($activeUsers);
        */
    }

    public function testCountTotalUsers(): void
    {
        // Exemple de test pour compter le nombre total d'utilisateurs
        $this->markTestSkipped('Méthode countTotalUsers() pas encore implémentée dans le repository');

        /*
        // Si vous ajoutez cette méthode au repository :
        $count = $this->repository->countTotalUsers();
        self::assertIsInt($count);
        self::assertGreaterThanOrEqual(0, $count);
        */
    }

    public function testFindUsersByRole(): void
    {
        // Exemple de test pour rechercher des utilisateurs par rôle
        $this->markTestSkipped('Méthode findUsersByRole() pas encore implémentée dans le repository');

        /*
        // Si vous ajoutez cette méthode au repository :
        $admins = $this->repository->findUsersByRole('ROLE_ADMIN');
        self::assertIsArray($admins);

        foreach ($admins as $admin) {
            self::assertInstanceOf(Utilisateur::class, $admin);
            self::assertContains('ROLE_ADMIN', $admin->getRoles());
        }
        */
    }
}
