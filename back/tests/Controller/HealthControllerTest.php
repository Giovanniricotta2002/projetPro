<?php

namespace App\Tests\Controller;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class HealthControllerTest extends WebTestCase
{
    private MockObject $connectionMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connectionMock = $this->createMock(Connection::class);
    }

    public function testHealthCheckSuccess(): void
    {
        // Arrange
        $client = static::createClient();
        
        // Mock successful database connection
        $this->connectionMock
            ->expects($this->once())
            ->method('executeQuery')
            ->with('SELECT 1')
            ->willReturn(true);

        $client->getContainer()->set(Connection::class, $this->connectionMock);

        // Act
        $client->request('GET', '/api/health');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertEquals('healthy', $responseData['status']);
        $this->assertEquals('MuscuScope Backend', $responseData['service']);
        $this->assertEquals('1.0.0', $responseData['version']);
        $this->assertEquals('connected', $responseData['database']);
        $this->assertArrayHasKey('timestamp', $responseData);
        $this->assertArrayHasKey('environment', $responseData);
    }

    public function testHealthCheckDatabaseFailure(): void
    {
        // Arrange
        $client = static::createClient();
        
        // Mock database connection failure
        $this->connectionMock
            ->expects($this->once())
            ->method('executeQuery')
            ->with('SELECT 1')
            ->willThrowException(new Exception('Database connection failed'));

        $client->getContainer()->set(Connection::class, $this->connectionMock);

        // Act
        $client->request('GET', '/api/health');

        // Assert
        $this->assertResponseStatusCodeSame(Response::HTTP_SERVICE_UNAVAILABLE);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertEquals('unhealthy', $responseData['status']);
        $this->assertEquals('disconnected', $responseData['database']);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Database connection failed', $responseData['error']);
    }

    public function testSimpleHealthCheck(): void
    {
        // Arrange
        $client = static::createClient();

        // Act
        $client->request('GET', '/health');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertEquals('healthy', $responseData['status']);
    }

    public function testHealthCheckResponseStructure(): void
    {
        // Arrange
        $client = static::createClient();
        
        $this->connectionMock
            ->expects($this->once())
            ->method('executeQuery')
            ->willReturn(true);

        $client->getContainer()->set(Connection::class, $this->connectionMock);

        // Act
        $client->request('GET', '/api/health');

        // Assert
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        // Vérifier la structure de la réponse
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('timestamp', $responseData);
        $this->assertArrayHasKey('service', $responseData);
        $this->assertArrayHasKey('version', $responseData);
        $this->assertArrayHasKey('database', $responseData);
        $this->assertArrayHasKey('environment', $responseData);
        
        // Vérifier les types
        $this->assertIsString($responseData['status']);
        $this->assertIsString($responseData['timestamp']);
        $this->assertIsString($responseData['service']);
        $this->assertIsString($responseData['version']);
        $this->assertIsString($responseData['database']);
    }

    public function testHealthCheckTimestampFormat(): void
    {
        // Arrange
        $client = static::createClient();
        
        $this->connectionMock
            ->expects($this->once())
            ->method('executeQuery')
            ->willReturn(true);

        $client->getContainer()->set(Connection::class, $this->connectionMock);

        // Act
        $client->request('GET', '/api/health');

        // Assert
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        // Vérifier que le timestamp est au format ISO 8601
        $timestamp = $responseData['timestamp'];
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/', $timestamp);
        
        // Vérifier que le timestamp peut être parsé
        $dateTime = \DateTime::createFromFormat('c', $timestamp);
        $this->assertInstanceOf(\DateTime::class, $dateTime);
    }
}
