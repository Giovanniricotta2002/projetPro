<?php

namespace App\Tests\Controller;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use App\Service\HttpOnlyCookieService;
use App\Service\JWTService;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TokenControllerTest extends WebTestCase
{
    private MockObject $jwtServiceMock;
    private MockObject $userRepositoryMock;
    private MockObject $cookieServiceMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwtServiceMock = $this->createMock(JWTService::class);
        $this->userRepositoryMock = $this->createMock(UtilisateurRepository::class);
        $this->cookieServiceMock = $this->createMock(HttpOnlyCookieService::class);
    }

    public function testRefreshTokenSuccess(): void
    {
        // Arrange
        $client = static::createClient();

        $user = new Utilisateur();
        $user->setUsername('testuser');

        $refreshToken = 'valid_refresh_token';
        $newAccessToken = 'new_access_token';
        $newRefreshToken = 'new_refresh_token';

        $this->jwtServiceMock
            ->expects($this->once())
            ->method('validateRefreshToken')
            ->with($refreshToken)
            ->willReturn($user);

        $this->jwtServiceMock
            ->expects($this->once())
            ->method('generateTokens')
            ->with($user)
            ->willReturn([
                'access_token' => $newAccessToken,
                'refresh_token' => $newRefreshToken,
                'expires_in' => 3600,
            ]);

        // Mock des services dans le conteneur
        $client->getContainer()->set(JWTService::class, $this->jwtServiceMock);
        $client->getContainer()->set(UtilisateurRepository::class, $this->userRepositoryMock);
        $client->getContainer()->set(HttpOnlyCookieService::class, $this->cookieServiceMock);

        // Act
        $client->request('POST', '/api/tokens/refresh', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'refresh_token' => $refreshToken,
        ]));

        // Assert
        $this->assertResponseIsSuccessful();
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('access_token', $responseData);
        $this->assertArrayHasKey('refresh_token', $responseData);
        $this->assertArrayHasKey('expires_in', $responseData);
    }

    public function testRefreshTokenInvalid(): void
    {
        // Arrange
        $client = static::createClient();

        $invalidRefreshToken = 'invalid_refresh_token';

        $this->jwtServiceMock
            ->expects($this->once())
            ->method('validateRefreshToken')
            ->with($invalidRefreshToken)
            ->willReturn(null);

        $client->getContainer()->set(JWTService::class, $this->jwtServiceMock);
        $client->getContainer()->set(UtilisateurRepository::class, $this->userRepositoryMock);
        $client->getContainer()->set(HttpOnlyCookieService::class, $this->cookieServiceMock);

        // Act
        $client->request('POST', '/api/tokens/refresh', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'refresh_token' => $invalidRefreshToken,
        ]));

        // Assert
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('INVALID_REFRESH_TOKEN', $responseData['error']);
    }

    public function testRefreshTokenMissingToken(): void
    {
        // Arrange
        $client = static::createClient();

        // Act
        $client->request('POST', '/api/tokens/refresh', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([]));

        // Assert
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $responseData);
    }

    public function testValidateTokenSuccess(): void
    {
        // Arrange
        $client = static::createClient();

        $validToken = 'valid_access_token';
        $user = new Utilisateur();
        $user->setUsername('testuser');

        $this->jwtServiceMock
            ->expects($this->once())
            ->method('validateToken')
            ->with($validToken)
            ->willReturn($user);

        $client->getContainer()->set(JWTService::class, $this->jwtServiceMock);

        // Act
        $client->request('POST', '/api/tokens/validate', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'token' => $validToken,
        ]));

        // Assert
        $this->assertResponseIsSuccessful();
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertTrue($responseData['valid']);
        $this->assertArrayHasKey('user', $responseData);
    }

    public function testValidateTokenInvalid(): void
    {
        // Arrange
        $client = static::createClient();

        $invalidToken = 'invalid_access_token';

        $this->jwtServiceMock
            ->expects($this->once())
            ->method('validateToken')
            ->with($invalidToken)
            ->willReturn(null);

        $client->getContainer()->set(JWTService::class, $this->jwtServiceMock);

        // Act
        $client->request('POST', '/api/tokens/validate', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'token' => $invalidToken,
        ]));

        // Assert
        $this->assertResponseIsSuccessful();
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertFalse($responseData['valid']);
    }

    public function testGetTokenInfoSuccess(): void
    {
        // Arrange
        $client = static::createClient();

        $token = 'valid_token';
        $tokenInfo = [
            'user_id' => 1,
            'username' => 'testuser',
            'roles' => ['ROLE_USER'],
            'exp' => time() + 3600,
            'iat' => time(),
        ];

        $this->jwtServiceMock
            ->expects($this->once())
            ->method('getTokenInfo')
            ->with($token)
            ->willReturn($tokenInfo);

        $client->getContainer()->set(JWTService::class, $this->jwtServiceMock);

        // Act
        $client->request('POST', '/api/tokens/info', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'token' => $token,
        ]));

        // Assert
        $this->assertResponseIsSuccessful();
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('user_id', $responseData);
        $this->assertArrayHasKey('username', $responseData);
        $this->assertArrayHasKey('roles', $responseData);
        $this->assertArrayHasKey('exp', $responseData);
        $this->assertArrayHasKey('iat', $responseData);
    }

    public function testClearTokensCookies(): void
    {
        // Arrange
        $client = static::createClient();

        $this->cookieServiceMock
            ->expects($this->once())
            ->method('clearJwtCookies')
            ->willReturn([
                'access_token' => null,
                'refresh_token' => null,
            ]);

        $client->getContainer()->set(HttpOnlyCookieService::class, $this->cookieServiceMock);

        // Act
        $client->request('POST', '/api/tokens/clear-cookies');

        // Assert
        $this->assertResponseIsSuccessful();
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Cookies cleared successfully', $responseData['message']);
    }
}
