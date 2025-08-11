<?php

namespace App\Tests\Service;

use App\Service\HttpOnlyCookieService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\{Cookie, Request, Response};

class HttpOnlyCookieServiceTest extends TestCase
{
    private HttpOnlyCookieService $cookieService;

    protected function setUp(): void
    {
        $this->cookieService = new HttpOnlyCookieService();
    }

    public function testSetJwtCookies(): void
    {
        $response = new Response();
        $request = Request::create('https://example.com/api/login', 'POST');
        $tokens = [
            'access_token' => 'access-token-value',
            'refresh_token' => 'refresh-token-value',
        ];

        $this->cookieService->setJwtCookies($response, $request, $tokens);

        $cookies = $response->headers->getCookies();
        $this->assertCount(2, $cookies);

        // Vérifier le cookie access_token
        $accessCookie = $cookies[0];
        $this->assertSame('access_token', $accessCookie->getName());
        $this->assertSame('access-token-value', $accessCookie->getValue());
        $this->assertTrue($accessCookie->isHttpOnly());
        $this->assertTrue($accessCookie->isSecure());
        $this->assertSame(Cookie::SAMESITE_STRICT, $accessCookie->getSameSite());

        // Vérifier le cookie refresh_token
        $refreshCookie = $cookies[1];
        $this->assertSame('refresh_token', $refreshCookie->getName());
        $this->assertSame('refresh-token-value', $refreshCookie->getValue());
        $this->assertTrue($refreshCookie->isHttpOnly());
        $this->assertTrue($refreshCookie->isSecure());
        $this->assertSame('/api/tokens/refresh', $refreshCookie->getPath());
    }

    public function testClearJwtCookies(): void
    {
        $response = new Response();
        $request = Request::create('https://example.com/api/logout', 'POST');

        $this->cookieService->clearJwtCookies($response, $request);

        $cookies = $response->headers->getCookies();
        $this->assertCount(2, $cookies);

        // Vérifier que les cookies sont expirés
        foreach ($cookies as $cookie) {
            $this->assertEmpty($cookie->getValue());
            $this->assertLessThan(time(), $cookie->getExpiresTime());
        }
    }

    public function testExtractAccessTokenFromCookies(): void
    {
        $request = Request::create('/api/protected');
        $request->cookies->set('access_token', 'token-from-cookie');

        $token = $this->cookieService->extractAccessToken($request);

        $this->assertSame('token-from-cookie', $token);
    }

    public function testExtractAccessTokenFromHeader(): void
    {
        $request = Request::create('/api/protected');
        $request->headers->set('Authorization', 'Bearer token-from-header');

        $token = $this->cookieService->extractAccessToken($request);

        $this->assertSame('token-from-header', $token);
    }

    public function testExtractAccessTokenPriorityOrder(): void
    {
        $request = Request::create('/api/protected');
        $request->cookies->set('access_token', 'token-from-cookie');
        $request->headers->set('Authorization', 'Bearer token-from-header');

        $token = $this->cookieService->extractAccessToken($request);

        // Le cookie doit avoir la priorité
        $this->assertSame('token-from-cookie', $token);
    }

    public function testExtractRefreshTokenFromCookies(): void
    {
        $request = Request::create('/api/tokens/refresh', 'POST');
        $request->cookies->set('refresh_token', 'refresh-from-cookie');

        $token = $this->cookieService->extractRefreshToken($request);

        $this->assertSame('refresh-from-cookie', $token);
    }

    public function testExtractRefreshTokenFromBody(): void
    {
        $request = Request::create(
            '/api/tokens/refresh',
            'POST',
            [],
            [],
            [],
            [],
            json_encode(['refresh_token' => 'refresh-from-body'])
        );

        $token = $this->cookieService->extractRefreshToken($request);

        $this->assertSame('refresh-from-body', $token);
    }

    public function testHasJwtCookies(): void
    {
        $request = Request::create('/api/protected');
        $this->assertFalse($this->cookieService->hasJwtCookies($request));

        $request->cookies->set('access_token', 'value');
        $this->assertTrue($this->cookieService->hasJwtCookies($request));
    }

    public function testGetTokenTtls(): void
    {
        $ttls = $this->cookieService->getTokenTtls();

        $this->assertArrayHasKey('access_token_ttl', $ttls);
        $this->assertArrayHasKey('refresh_token_ttl', $ttls);
        $this->assertSame(3600, $ttls['access_token_ttl']);
        $this->assertSame(604800, $ttls['refresh_token_ttl']);
    }

    public function testInsecureRequestCookies(): void
    {
        $response = new Response();
        $request = Request::create('http://example.com/api/login', 'POST'); // HTTP, not HTTPS
        $tokens = [
            'access_token' => 'access-token-value',
            'refresh_token' => 'refresh-token-value',
        ];

        $this->cookieService->setJwtCookies($response, $request, $tokens);

        $cookies = $response->headers->getCookies();

        // Les cookies ne doivent pas être secure sur HTTP
        foreach ($cookies as $cookie) {
            $this->assertFalse($cookie->isSecure());
        }
    }
}
