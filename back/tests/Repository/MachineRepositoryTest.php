<?php

namespace App\Tests\Repository;

use App\Entity\Machine;
use App\Repository\MachineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Uuid;

class MachineRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private MachineRepository $repository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        
        $this->repository = $this->entityManager->getRepository(Machine::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }

    public function testRepository(): void
    {
        // Test que le repository est bien une instance de MachineRepository
        self::assertInstanceOf(MachineRepository::class, $this->repository);
    }

    public function testBasicEntityOperations(): void
    {
        // Test des opérations CRUD de base
        $machine = new Machine();
        $machine->setName('Machine Test');
        $machine->setVisible(true);
        
        // Persist
        $this->entityManager->persist($machine);
        $this->entityManager->flush();
        
        // Vérifier que l'ID a été assigné
        self::assertNotNull($machine->getId());
        self::assertInstanceOf(Uuid::class, $machine->getUuid());
        
        // Find
        $foundMachine = $this->repository->find($machine->getId());
        self::assertNotNull($foundMachine);
        self::assertEquals('Machine Test', $foundMachine->getName());
        
        // Clean up
        $this->entityManager->remove($foundMachine);
        $this->entityManager->flush();
    }

    public function testFindByUuid(): void
    {
        // Exemple de méthode personnalisée pour rechercher par UUID
        $this->markTestSkipped('Méthode findByUuid() pas encore implémentée dans le repository');
        
        /*
        // Si vous ajoutez cette méthode au repository :
        public function findByUuid(Uuid $uuid): ?Machine
        {
            return $this->createQueryBuilder('m')
                ->andWhere('m.uuid = :uuid')
                ->setParameter('uuid', $uuid)
                ->getQuery()
                ->getOneOrNullResult();
        }
        
        // Test :
        $uuid = Uuid::v4();
        $machine = new Machine();
        $machine->setUuid($uuid);
        $machine->setName('Test Machine');
        
        $this->entityManager->persist($machine);
        $this->entityManager->flush();
        
        $foundMachine = $this->repository->findByUuid($uuid);
        self::assertNotNull($foundMachine);
        self::assertEquals($uuid, $foundMachine->getUuid());
        
        // Clean up
        $this->entityManager->remove($foundMachine);
        $this->entityManager->flush();
        */
    }

    public function testFindVisibleMachines(): void
    {
        // Exemple de méthode pour trouver les machines visibles
        $this->markTestSkipped('Méthode findVisibleMachines() pas encore implémentée dans le repository');
        
        /*
        // Si vous ajoutez cette méthode au repository :
        public function findVisibleMachines(): array
        {
            return $this->createQueryBuilder('m')
                ->andWhere('m.visible = :visible')
                ->setParameter('visible', true)
                ->orderBy('m.name', 'ASC')
                ->getQuery()
                ->getResult();
        }
        
        // Test :
        $visibleMachines = $this->repository->findVisibleMachines();
        self::assertIsArray($visibleMachines);
        
        foreach ($visibleMachines as $machine) {
            self::assertInstanceOf(Machine::class, $machine);
            self::assertTrue($machine->isVisible());
        }
        */
    }

    public function testFindMachinesCreatedAfter(): void
    {
        // Exemple de méthode pour trouver les machines créées après une date
        $this->markTestSkipped('Méthode findMachinesCreatedAfter() pas encore implémentée dans le repository');
        
        /*
        // Si vous ajoutez cette méthode au repository :
        public function findMachinesCreatedAfter(\DateTime $date): array
        {
            return $this->createQueryBuilder('m')
                ->andWhere('m.dateCreation > :date')
                ->setParameter('date', $date)
                ->orderBy('m.dateCreation', 'DESC')
                ->getQuery()
                ->getResult();
        }
        
        // Test :
        $date = new \DateTime('-1 month');
        $recentMachines = $this->repository->findMachinesCreatedAfter($date);
        self::assertIsArray($recentMachines);
        
        foreach ($recentMachines as $machine) {
            self::assertInstanceOf(Machine::class, $machine);
            self::assertGreaterThan($date, $machine->getDateCreation());
        }
        */
    }

    public function testSearchMachinesByName(): void
    {
        // Exemple de recherche de machines par nom
        $this->markTestSkipped('Méthode searchMachinesByName() pas encore implémentée dans le repository');
        
        /*
        // Si vous ajoutez cette méthode au repository :
        public function searchMachinesByName(string $searchTerm): array
        {
            return $this->createQueryBuilder('m')
                ->andWhere('m.name LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . $searchTerm . '%')
                ->orderBy('m.name', 'ASC')
                ->getQuery()
                ->getResult();
        }
        
        // Test :
        $results = $this->repository->searchMachinesByName('test');
        self::assertIsArray($results);
        
        foreach ($results as $machine) {
            self::assertInstanceOf(Machine::class, $machine);
            self::assertStringContainsStringIgnoringCase('test', $machine->getName());
        }
        */
    }

    public function testCountMachinesByVisibility(): void
    {
        // Exemple de comptage de machines par visibilité
        $this->markTestSkipped('Méthode countMachinesByVisibility() pas encore implémentée dans le repository');
        
        /*
        // Si vous ajoutez cette méthode au repository :
        public function countMachinesByVisibility(bool $visible): int
        {
            return $this->createQueryBuilder('m')
                ->select('COUNT(m.id)')
                ->andWhere('m.visible = :visible')
                ->setParameter('visible', $visible)
                ->getQuery()
                ->getSingleScalarResult();
        }
        
        // Test :
        $visibleCount = $this->repository->countMachinesByVisibility(true);
        $hiddenCount = $this->repository->countMachinesByVisibility(false);
        
        self::assertIsInt($visibleCount);
        self::assertIsInt($hiddenCount);
        self::assertGreaterThanOrEqual(0, $visibleCount);
        self::assertGreaterThanOrEqual(0, $hiddenCount);
        */
    }

    public function testFindMachinesWithoutForum(): void
    {
        // Exemple de méthode pour trouver les machines sans forum associé
        $this->markTestSkipped('Méthode findMachinesWithoutForum() pas encore implémentée dans le repository');
        
        /*
        // Si vous ajoutez cette méthode au repository :
        public function findMachinesWithoutForum(): array
        {
            return $this->createQueryBuilder('m')
                ->andWhere('m.forum IS NULL')
                ->orderBy('m.dateCreation', 'DESC')
                ->getQuery()
                ->getResult();
        }
        
        // Test :
        $machinesWithoutForum = $this->repository->findMachinesWithoutForum();
        self::assertIsArray($machinesWithoutForum);
        
        foreach ($machinesWithoutForum as $machine) {
            self::assertInstanceOf(Machine::class, $machine);
            self::assertNull($machine->getForum());
        }
        */
    }
}
