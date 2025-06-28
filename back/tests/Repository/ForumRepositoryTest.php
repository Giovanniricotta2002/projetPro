<?php

namespace App\Tests\Repository;

use App\Entity\Forum;
use App\Entity\Utilisateur;
use App\Repository\ForumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ForumRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private ForumRepository $repository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        
        $this->repository = $this->entityManager->getRepository(Forum::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }

    public function testRepository(): void
    {
        // Test que le repository est bien une instance de ForumRepository
        self::assertInstanceOf(ForumRepository::class, $this->repository);
    }

    public function testBasicEntityOperations(): void
    {
        // Test des opérations CRUD de base
        $forum = new Forum();
        $forum->setTitre('Forum Test');
        $forum->setDateCreation(new \DateTime());
        $forum->setOrdreAffichage(1);
        $forum->setVisible(true);
        
        // Persist
        $this->entityManager->persist($forum);
        $this->entityManager->flush();
        
        // Vérifier que l'ID a été assigné
        self::assertNotNull($forum->getId());
        
        // Find
        $foundForum = $this->repository->find($forum->getId());
        self::assertNotNull($foundForum);
        self::assertEquals('Forum Test', $foundForum->getTitre());
        
        // Update
        $foundForum->setTitre('Forum Test Modifié');
        $this->entityManager->flush();
        
        $updatedForum = $this->repository->find($forum->getId());
        self::assertEquals('Forum Test Modifié', $updatedForum->getTitre());
        
        // Delete
        $this->entityManager->remove($updatedForum);
        $this->entityManager->flush();
        
        $deletedForum = $this->repository->find($forum->getId());
        self::assertNull($deletedForum);
    }

    public function testFindVisibleForums(): void
    {
        // Exemple de méthode personnalisée à implémenter
        $this->markTestSkipped('Méthode findVisibleForums() pas encore implémentée dans le repository');
        
        /*
        // Si vous ajoutez cette méthode au repository :
        public function findVisibleForums(): array
        {
            return $this->createQueryBuilder('f')
                ->andWhere('f.visible = :visible')
                ->setParameter('visible', true)
                ->orderBy('f.ordreAffichage', 'ASC')
                ->getQuery()
                ->getResult();
        }
        
        // Test :
        $visibleForums = $this->repository->findVisibleForums();
        self::assertIsArray($visibleForums);
        
        foreach ($visibleForums as $forum) {
            self::assertInstanceOf(Forum::class, $forum);
            self::assertTrue($forum->isVisible());
        }
        */
    }

    public function testFindForumsByUser(): void
    {
        // Exemple de méthode pour trouver les forums d'un utilisateur
        $this->markTestSkipped('Méthode findForumsByUser() pas encore implémentée dans le repository');
        
        /*
        // Si vous ajoutez cette méthode au repository :
        public function findForumsByUser(Utilisateur $user): array
        {
            return $this->createQueryBuilder('f')
                ->andWhere('f.utilisateur = :user')
                ->setParameter('user', $user)
                ->orderBy('f.dateCreation', 'DESC')
                ->getQuery()
                ->getResult();
        }
        
        // Test :
        $user = new Utilisateur();
        $userForums = $this->repository->findForumsByUser($user);
        self::assertIsArray($userForums);
        */
    }

    public function testFindActiveForums(): void
    {
        // Exemple pour trouver les forums actifs (non clos)
        $this->markTestSkipped('Méthode findActiveForums() pas encore implémentée dans le repository');
        
        /*
        // Si vous ajoutez cette méthode au repository :
        public function findActiveForums(): array
        {
            return $this->createQueryBuilder('f')
                ->andWhere('f.dateCloture IS NULL OR f.dateCloture > :now')
                ->setParameter('now', new \DateTime())
                ->andWhere('f.visible = :visible')
                ->setParameter('visible', true)
                ->orderBy('f.ordreAffichage', 'ASC')
                ->getQuery()
                ->getResult();
        }
        
        // Test :
        $activeForums = $this->repository->findActiveForums();
        self::assertIsArray($activeForums);
        
        foreach ($activeForums as $forum) {
            self::assertInstanceOf(Forum::class, $forum);
            self::assertTrue($forum->isVisible());
            
            $dateCloture = $forum->getDateCloture();
            if ($dateCloture !== null) {
                self::assertGreaterThan(new \DateTime(), $dateCloture);
            }
        }
        */
    }

    public function testCountForums(): void
    {
        // Exemple pour compter les forums
        $this->markTestSkipped('Méthode countForums() pas encore implémentée dans le repository');
        
        /*
        // Si vous ajoutez cette méthode au repository :
        public function countForums(): int
        {
            return $this->createQueryBuilder('f')
                ->select('COUNT(f.id)')
                ->getQuery()
                ->getSingleScalarResult();
        }
        
        // Test :
        $count = $this->repository->countForums();
        self::assertIsInt($count);
        self::assertGreaterThanOrEqual(0, $count);
        */
    }

    public function testSearchForumsByTitle(): void
    {
        // Exemple de recherche de forums par titre
        $this->markTestSkipped('Méthode searchForumsByTitle() pas encore implémentée dans le repository');
        
        /*
        // Si vous ajoutez cette méthode au repository :
        public function searchForumsByTitle(string $searchTerm): array
        {
            return $this->createQueryBuilder('f')
                ->andWhere('f.titre LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . $searchTerm . '%')
                ->orderBy('f.titre', 'ASC')
                ->getQuery()
                ->getResult();
        }
        
        // Test :
        $results = $this->repository->searchForumsByTitle('test');
        self::assertIsArray($results);
        
        foreach ($results as $forum) {
            self::assertInstanceOf(Forum::class, $forum);
            self::assertStringContainsStringIgnoringCase('test', $forum->getTitre());
        }
        */
    }
}
