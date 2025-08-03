<?php

namespace App\Tests\Service;

use App\Entity\LogLogin;
use App\Service\LoginLoggerService;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class LoginLoggerServiceTest extends TestCase
{
    private LoginLoggerService $service;
    private EntityManagerInterface $entityManager;
    private RequestStack $requestStack;
    private EntityRepository $repository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->repository = $this->createMock(EntityRepository::class);

        $this->entityManager->method('getRepository')
            ->with(LogLogin::class)
            ->willReturn($this->repository);

        $this->service = new LoginLoggerService(
            $this->entityManager,
            $this->requestStack
        );
    }

    public function testLogSuccessfulLogin(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('getClientIp')->willReturn('192.168.1.1');

        $this->requestStack->method('getCurrentRequest')->willReturn($request);

        $this->entityManager->expects(self::once())
            ->method('persist')
            ->with(self::isInstanceOf(LogLogin::class));

        $this->entityManager->expects(self::once())
            ->method('flush');

        $result = $this->service->logSuccessfulLogin('testuser');

        self::assertInstanceOf(LogLogin::class, $result);
        self::assertEquals('testuser', $result->getLogin());
        self::assertTrue($result->isSuccess());
        self::assertEquals('192.168.1.1', $result->getIpPublic());
    }

    public function testLogFailedLogin(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('getClientIp')->willReturn('192.168.1.100');

        $this->requestStack->method('getCurrentRequest')->willReturn($request);

        $this->entityManager->expects(self::once())
            ->method('persist')
            ->with(self::isInstanceOf(LogLogin::class));

        $this->entityManager->expects(self::once())
            ->method('flush');

        $result = $this->service->logFailedLogin('wronguser');

        self::assertInstanceOf(LogLogin::class, $result);
        self::assertEquals('wronguser', $result->getLogin());
        self::assertFalse($result->isSuccess());
        self::assertEquals('192.168.1.100', $result->getIpPublic());
    }

    public function testLogLoginAttemptWithCustomDate(): void
    {
        $customDate = new \DateTime('2024-01-01 12:00:00');
        $request = $this->createMock(Request::class);
        $request->method('getClientIp')->willReturn('10.0.0.1');

        $this->requestStack->method('getCurrentRequest')->willReturn($request);

        $this->entityManager->expects(self::once())
            ->method('persist')
            ->with(self::isInstanceOf(LogLogin::class));

        $this->entityManager->expects(self::once())
            ->method('flush');

        $result = $this->service->logLoginAttempt('customuser', true, $customDate);

        self::assertInstanceOf(LogLogin::class, $result);
        self::assertEquals('customuser', $result->getLogin());
        self::assertTrue($result->isSuccess());
        self::assertEquals('10.0.0.1', $result->getIpPublic());
        self::assertEquals($customDate, $result->getDate());
    }

    public function testLogLoginAttemptWithoutRequest(): void
    {
        $this->requestStack->method('getCurrentRequest')->willReturn(null);

        $this->entityManager->expects(self::once())
            ->method('persist')
            ->with(self::isInstanceOf(LogLogin::class));

        $this->entityManager->expects(self::once())
            ->method('flush');

        $result = $this->service->logLoginAttempt('noipuser', false);

        self::assertInstanceOf(LogLogin::class, $result);
        self::assertEquals('noipuser', $result->getLogin());
        self::assertFalse($result->isSuccess());
        self::assertEquals('0.0.0.0', $result->getIpPublic()); // IP par défaut
    }

    public function testCountRecentFailedAttempts(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(AbstractQuery::class);

        $this->repository->method('createQueryBuilder')
            ->with('l')
            ->willReturn($queryBuilder);

        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('andWhere')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);

        $query->method('getSingleScalarResult')->willReturn(3);

        $result = $this->service->countRecentFailedAttempts('192.168.1.100', 60);

        self::assertEquals(3, $result);
    }

    public function testCountRecentFailedAttemptsForLogin(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(AbstractQuery::class);

        $this->repository->method('createQueryBuilder')
            ->with('l')
            ->willReturn($queryBuilder);

        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('andWhere')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);

        $query->method('getSingleScalarResult')->willReturn(2);

        $result = $this->service->countRecentFailedAttemptsForLogin('testuser', 30);

        self::assertEquals(2, $result);
    }

    public function testIsIpBlocked(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(AbstractQuery::class);

        $this->repository->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('andWhere')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);

        // Test avec 5 tentatives (seuil par défaut)
        $query->method('getSingleScalarResult')->willReturn(5);
        $result = $this->service->isIpBlocked('192.168.1.100');
        self::assertTrue($result);

        // Test avec 3 tentatives (en dessous du seuil)
        $query->method('getSingleScalarResult')->willReturn(3);
        $result = $this->service->isIpBlocked('192.168.1.100');
        self::assertFalse($result);
    }

    public function testIsLoginBlocked(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(AbstractQuery::class);

        $this->repository->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('andWhere')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);

        // Test avec 3 tentatives (seuil par défaut pour login)
        $query->method('getSingleScalarResult')->willReturn(3);
        $result = $this->service->isLoginBlocked('testuser');
        self::assertTrue($result);

        // Test avec 2 tentatives (en dessous du seuil)
        $query->method('getSingleScalarResult')->willReturn(2);
        $result = $this->service->isLoginBlocked('testuser');
        self::assertFalse($result);
    }

    public function testGetLoginStatistics(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(AbstractQuery::class);

        $this->repository->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('addSelect')->willReturnSelf();
        $queryBuilder->method('andWhere')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);

        $query->method('getSingleResult')->willReturn([
            'total' => '10',
            'successful' => '8',
            'failed' => '2',
        ]);

        $from = new \DateTime('-1 week');
        $to = new \DateTime();

        $result = $this->service->getLoginStatistics($from, $to);

        self::assertIsArray($result);
        self::assertEquals(10, $result['total']);
        self::assertEquals(8, $result['successful']);
        self::assertEquals(2, $result['failed']);
        self::assertEquals(80.0, $result['success_rate']);
    }

    public function testGetLoginStatisticsWithZeroTotal(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(AbstractQuery::class);

        $this->repository->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('addSelect')->willReturnSelf();
        $queryBuilder->method('andWhere')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);

        $query->method('getSingleResult')->willReturn([
            'total' => '0',
            'successful' => '0',
            'failed' => '0',
        ]);

        $from = new \DateTime('-1 week');
        $to = new \DateTime();

        $result = $this->service->getLoginStatistics($from, $to);

        self::assertIsArray($result);
        self::assertEquals(0, $result['total']);
        self::assertEquals(0, $result['successful']);
        self::assertEquals(0, $result['failed']);
        self::assertEquals(0, $result['success_rate']); // Évite la division par zéro
    }
}
