<?php

namespace App\Tests\Service;

use App\Service\JWTService;
use App\Tests\IntegrationTestCase;

/**
 * Test d'intégration pour JWTService.
 */
final class JWTServiceIntegrationTest extends IntegrationTestCase
{
    private JWTService $jwtService;

    protected function setUp(): void
    {
        parent::setUp();

        // Récupère le service JWT depuis le container
        $this->jwtService = $this->getService(JWTService::class);
    }

    public function testServiceIsInstantiated(): void
    {
        $this->assertInstanceOf(JWTService::class, $this->jwtService);
    }

    public function testExtractTokenFromHeaderWithValidBearer(): void
    {
        $token = 'some.jwt.token';
        $authHeader = 'Bearer ' . $token;

        $extractedToken = $this->jwtService->extractTokenFromHeader($authHeader);

        $this->assertSame($token, $extractedToken);
    }

    public function testExtractTokenFromHeaderWithInvalidFormat(): void
    {
        $extractedToken = $this->jwtService->extractTokenFromHeader('Invalid header');

        $this->assertNull($extractedToken);
    }

    public function testExtractTokenFromHeaderWithNull(): void
    {
        $extractedToken = $this->jwtService->extractTokenFromHeader(null);

        $this->assertNull($extractedToken);
    }
}
