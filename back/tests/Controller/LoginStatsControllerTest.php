<?php

namespace App\Tests\Controller;

use App\Entity\Utilisateur;
use App\Service\LoginLoggerService;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class LoginStatsControllerTest extends WebTestCase
{
    private MockObject $loginLoggerMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginLoggerMock = $this->createMock(LoginLoggerService::class);
    }

    public function testGetStatisticsSuccess(): void
    {
        // Arrange
        $client = static::createClient();

        $mockStatistics = [
            'total_logins' => 150,
            'unique_users' => 45,
            'failed_attempts' => 12,
            'success_rate' => 92.5,
            'daily_breakdown' => [
                '2025-07-01' => ['successful' => 20, 'failed' => 2],
                '2025-07-02' => ['successful' => 25, 'failed' => 1],
                '2025-07-03' => ['successful' => 18, 'failed' => 3],
            ],
        ];

        $this->loginLoggerMock
            ->expects($this->once())
            ->method('getLoginStatistics')
            ->willReturn($mockStatistics);

        $client->getContainer()->set(LoginLoggerService::class, $this->loginLoggerMock);

        // Créer un utilisateur admin pour l'authentification
        $client->loginUser($this->createAdminUser());

        // Act
        $client->request('GET', '/api/admin/login-logs/statistics');

        // Assert
        $this->assertResponseIsSuccessful();
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertTrue($responseData['success']);
        $this->assertEquals($mockStatistics, $responseData['statistics']);
        $this->assertArrayHasKey('period', $responseData);
        $this->assertArrayHasKey('from', $responseData['period']);
        $this->assertArrayHasKey('to', $responseData['period']);
    }

    public function testGetStatisticsWithCustomPeriod(): void
    {
        // Arrange
        $client = static::createClient();

        $fromDate = '2025-06-01';
        $toDate = '2025-06-30';

        $this->loginLoggerMock
            ->expects($this->once())
            ->method('getLoginStatistics')
            ->with(
                $this->callback(function ($from) use ($fromDate) {
                    return $from->format('Y-m-d') === $fromDate;
                }),
                $this->callback(function ($to) use ($toDate) {
                    return $to->format('Y-m-d') === $toDate;
                })
            )
            ->willReturn(['total_logins' => 100]);

        $client->getContainer()->set(LoginLoggerService::class, $this->loginLoggerMock);
        $client->loginUser($this->createAdminUser());

        // Act
        $client->request('GET', '/api/admin/login-logs/statistics', [
            'from' => $fromDate,
            'to' => $toDate,
        ]);

        // Assert
        $this->assertResponseIsSuccessful();
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertTrue($responseData['success']);
        $this->assertEquals($fromDate . ' 00:00:00', $responseData['period']['from']);
        $this->assertEquals($toDate . ' 00:00:00', $responseData['period']['to']);
    }

    public function testGetStatisticsUnauthorized(): void
    {
        // Arrange
        $client = static::createClient();

        // Act - sans authentification
        $client->request('GET', '/api/admin/login-logs/statistics');

        // Assert
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetStatisticsInsufficientRole(): void
    {
        // Arrange
        $client = static::createClient();

        // Connecter un utilisateur normal (pas admin)
        $client->loginUser($this->createRegularUser());

        // Act
        $client->request('GET', '/api/admin/login-logs/statistics');

        // Assert
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testGetStatisticsServiceException(): void
    {
        // Arrange
        $client = static::createClient();

        $this->loginLoggerMock
            ->expects($this->once())
            ->method('getLoginStatistics')
            ->willThrowException(new \Exception('Database error'));

        $client->getContainer()->set(LoginLoggerService::class, $this->loginLoggerMock);
        $client->loginUser($this->createAdminUser());

        // Act
        $client->request('GET', '/api/admin/login-logs/statistics');

        // Assert
        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertFalse($responseData['success']);
        $this->assertArrayHasKey('error', $responseData);
    }

    public function testGetRecentLogsSuccess(): void
    {
        // Arrange
        $client = static::createClient();

        $mockLogs = [
            [
                'id' => 1,
                'username' => 'user1',
                'ip_address' => '192.168.1.1',
                'user_agent' => 'Mozilla/5.0...',
                'success' => true,
                'login_time' => '2025-07-03T10:30:00Z',
            ],
            [
                'id' => 2,
                'username' => 'user2',
                'ip_address' => '192.168.1.2',
                'user_agent' => 'Chrome/91.0...',
                'success' => false,
                'login_time' => '2025-07-03T09:45:00Z',
            ],
        ];

        $this->loginLoggerMock
            ->expects($this->once())
            ->method('getRecentLoginLogs')
            ->with(50) // limite par défaut
            ->willReturn($mockLogs);

        $client->getContainer()->set(LoginLoggerService::class, $this->loginLoggerMock);
        $client->loginUser($this->createAdminUser());

        // Act
        $client->request('GET', '/api/admin/login-logs/recent');

        // Assert
        $this->assertResponseIsSuccessful();
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertTrue($responseData['success']);
        $this->assertEquals($mockLogs, $responseData['logs']);
    }

    public function testGetRecentLogsWithCustomLimit(): void
    {
        // Arrange
        $client = static::createClient();
        $limit = 25;

        $this->loginLoggerMock
            ->expects($this->once())
            ->method('getRecentLoginLogs')
            ->with($limit)
            ->willReturn([]);

        $client->getContainer()->set(LoginLoggerService::class, $this->loginLoggerMock);
        $client->loginUser($this->createAdminUser());

        // Act
        $client->request('GET', '/api/admin/login-logs/recent', [
            'limit' => $limit,
        ]);

        // Assert
        $this->assertResponseIsSuccessful();
    }

    public function testGetFailedAttemptsSuccess(): void
    {
        // Arrange
        $client = static::createClient();

        $mockFailedAttempts = [
            [
                'ip_address' => '192.168.1.100',
                'attempts_count' => 5,
                'last_attempt' => '2025-07-03T11:00:00Z',
                'usernames_tried' => ['admin', 'root', 'user'],
            ],
            [
                'ip_address' => '10.0.0.50',
                'attempts_count' => 3,
                'last_attempt' => '2025-07-03T10:30:00Z',
                'usernames_tried' => ['test', 'demo'],
            ],
        ];

        $this->loginLoggerMock
            ->expects($this->once())
            ->method('getFailedLoginAttempts')
            ->willReturn($mockFailedAttempts);

        $client->getContainer()->set(LoginLoggerService::class, $this->loginLoggerMock);
        $client->loginUser($this->createAdminUser());

        // Act
        $client->request('GET', '/api/admin/login-logs/failed-attempts');

        // Assert
        $this->assertResponseIsSuccessful();
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertTrue($responseData['success']);
        $this->assertEquals($mockFailedAttempts, $responseData['failed_attempts']);
    }

    private function createAdminUser(): object
    {
        return $this->createMock(Utilisateur::class);
        // Mock d'un utilisateur avec ROLE_ADMIN
        // En réalité, vous devrez adapter selon votre système d'auth
    }

    private function createRegularUser(): object
    {
        return $this->createMock(Utilisateur::class);
        // Mock d'un utilisateur avec ROLE_USER seulement
    }
}
