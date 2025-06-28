<?php

namespace App\Tests\Repository;

use App\Entity\LogLogin;
use App\Repository\LogLoginRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LogLoginRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private LogLoginRepository $repository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        
        $this->repository = $this->entityManager->getRepository(LogLogin::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }

    public function testRepository(): void
    {
        // Test que le repository est bien une instance de LogLoginRepository
        self::assertInstanceOf(LogLoginRepository::class, $this->repository);
    }

    public function testBasicEntityOperations(): void
    {
        // Test des opérations CRUD de base
        $logLogin = new LogLogin();
        $logLogin->setLogin('testuser');
        $logLogin->setIpPublic('192.168.1.1');
        $logLogin->setSuccess(true);
        
        // Persist
        $this->entityManager->persist($logLogin);
        $this->entityManager->flush();
        
        // Vérifier que l'ID a été assigné
        self::assertNotNull($logLogin->getId());
        
        // Find
        $foundLog = $this->repository->find($logLogin->getId());
        self::assertNotNull($foundLog);
        self::assertEquals('testuser', $foundLog->getLogin());
        self::assertEquals('192.168.1.1', $foundLog->getIpPublic());
        self::assertTrue($foundLog->isSuccess());
        
        // Clean up
        $this->entityManager->remove($foundLog);
        $this->entityManager->flush();
    }

    public function testFindSuccessfulLogins(): void
    {
        // Exemple de méthode personnalisée
        $this->markTestSkipped('Méthode findSuccessfulLogins() pas encore implémentée dans le repository');
        
        /*
        // Si vous ajoutez cette méthode au repository :
        public function findSuccessfulLogins(\DateTime $since = null): array
        {
            $qb = $this->createQueryBuilder('l')
                ->andWhere('l.success = :success')
                ->setParameter('success', true)
                ->orderBy('l.date', 'DESC');
            
            if ($since) {
                $qb->andWhere('l.date >= :since')
                   ->setParameter('since', $since);
            }
            
            return $qb->getQuery()->getResult();
        }
        
        // Test :
        $successfulLogins = $this->repository->findSuccessfulLogins();
        self::assertIsArray($successfulLogins);
        
        foreach ($successfulLogins as $log) {
            self::assertInstanceOf(LogLogin::class, $log);
            self::assertTrue($log->isSuccess());
        }
        */
    }

    public function testFindFailedLoginsByIp(): void
    {
        // Exemple de méthode pour détecter des tentatives suspectes
        $this->markTestSkipped('Méthode findFailedLoginsByIp() pas encore implémentée dans le repository');
        
        /*
        // Si vous ajoutez cette méthode au repository :
        public function findFailedLoginsByIp(string $ipAddress, \DateTime $since = null): array
        {
            $qb = $this->createQueryBuilder('l')
                ->andWhere('l.ipPublic = :ip')
                ->andWhere('l.success = :success')
                ->setParameter('ip', $ipAddress)
                ->setParameter('success', false)
                ->orderBy('l.date', 'DESC');
            
            if ($since) {
                $qb->andWhere('l.date >= :since')
                   ->setParameter('since', $since);
            }
            
            return $qb->getQuery()->getResult();
        }
        
        // Test :
        $failedAttempts = $this->repository->findFailedLoginsByIp('192.168.1.100');
        self::assertIsArray($failedAttempts);
        
        foreach ($failedAttempts as $log) {
            self::assertInstanceOf(LogLogin::class, $log);
            self::assertFalse($log->isSuccess());
            self::assertEquals('192.168.1.100', $log->getIpPublic());
        }
        */
    }

    public function testCountLoginAttemptsLastHour(): void
    {
        // Exemple de méthode pour limiter les tentatives de connexion
        $this->markTestSkipped('Méthode countLoginAttemptsLastHour() pas encore implémentée dans le repository');
        
        /*
        // Si vous ajoutez cette méthode au repository :
        public function countLoginAttemptsLastHour(string $ipAddress): int
        {
            $oneHourAgo = new \DateTime('-1 hour');
            
            return $this->createQueryBuilder('l')
                ->select('COUNT(l.id)')
                ->andWhere('l.ipPublic = :ip')
                ->andWhere('l.date >= :oneHourAgo')
                ->setParameter('ip', $ipAddress)
                ->setParameter('oneHourAgo', $oneHourAgo)
                ->getQuery()
                ->getSingleScalarResult();
        }
        
        // Test :
        $count = $this->repository->countLoginAttemptsLastHour('192.168.1.1');
        self::assertIsInt($count);
        self::assertGreaterThanOrEqual(0, $count);
        */
    }

    public function testGetLoginStatistics(): void
    {
        // Exemple de méthode pour obtenir des statistiques
        $this->markTestSkipped('Méthode getLoginStatistics() pas encore implémentée dans le repository');
        
        /*
        // Si vous ajoutez cette méthode au repository :
        public function getLoginStatistics(\DateTime $from, \DateTime $to): array
        {
            $result = $this->createQueryBuilder('l')
                ->select('COUNT(l.id) as total')
                ->addSelect('SUM(CASE WHEN l.success = true THEN 1 ELSE 0 END) as successful')
                ->addSelect('SUM(CASE WHEN l.success = false THEN 1 ELSE 0 END) as failed')
                ->andWhere('l.date BETWEEN :from AND :to')
                ->setParameter('from', $from)
                ->setParameter('to', $to)
                ->getQuery()
                ->getSingleResult();
            
            return [
                'total' => (int) $result['total'],
                'successful' => (int) $result['successful'],
                'failed' => (int) $result['failed'],
                'success_rate' => $result['total'] > 0 ? 
                    round(($result['successful'] / $result['total']) * 100, 2) : 0
            ];
        }
        
        // Test :
        $from = new \DateTime('-1 week');
        $to = new \DateTime();
        $stats = $this->repository->getLoginStatistics($from, $to);
        
        self::assertIsArray($stats);
        self::assertArrayHasKey('total', $stats);
        self::assertArrayHasKey('successful', $stats);
        self::assertArrayHasKey('failed', $stats);
        self::assertArrayHasKey('success_rate', $stats);
        self::assertIsInt($stats['total']);
        self::assertIsInt($stats['successful']);
        self::assertIsInt($stats['failed']);
        self::assertIsFloat($stats['success_rate']);
        */
    }
}
