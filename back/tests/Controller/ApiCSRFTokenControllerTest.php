<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class ApiCSRFTokenControllerTest extends WebTestCase
{
    public function testGenerateTokenSuccess(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/csrfToken');

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        self::assertArrayHasKey('csrfToken', $responseData);
        self::assertNotEmpty($responseData['csrfToken']);
        self::assertIsString($responseData['csrfToken']);
    }

    public function testGenerateTokenWithOptionsMethod(): void
    {
        $client = static::createClient();
        $client->request('OPTIONS', '/api/csrfToken');

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        self::assertArrayHasKey('csrfToken', $responseData);
        self::assertNotEmpty($responseData['csrfToken']);
    }

    public function testVerifyTokenSuccess(): void
    {
        $client = static::createClient();
        
        // D'abord, générer un token CSRF valide
        $client->request('GET', '/api/csrfToken');
        $tokenResponse = json_decode($client->getResponse()->getContent(), true);
        $csrfToken = $tokenResponse['csrfToken'];

        // Ensuite, vérifier le token
        $client->request(
            'POST',
            '/api/csrfToken/verify',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['csrfToken' => $csrfToken])
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testVerifyTokenMissingToken(): void
    {
        $client = static::createClient();
        
        $client->request(
            'POST',
            '/api/csrfToken/verify',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        self::assertArrayHasKey('error', $responseData);
        self::assertEquals('CSRF token is missing', $responseData['error']);
    }

    public function testVerifyTokenEmptyToken(): void
    {
        $client = static::createClient();
        
        $client->request(
            'POST',
            '/api/csrfToken/verify',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['csrfToken' => ''])
        );

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        self::assertArrayHasKey('error', $responseData);
        self::assertEquals('Invalid CSRF token', $responseData['error']);
    }

    public function testVerifyTokenInvalidToken(): void
    {
        $client = static::createClient();
        
        $client->request(
            'POST',
            '/api/csrfToken/verify',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['csrfToken' => 'invalid-token-12345'])
        );

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        self::assertArrayHasKey('error', $responseData);
        self::assertEquals('Invalid CSRF token', $responseData['error']);
    }

    public function testVerifyTokenWithMalformedJson(): void
    {
        $client = static::createClient();
        
        $client->request(
            'POST',
            '/api/csrfToken/verify',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"csrfToken": invalid-json}'
        );

        // Le test devrait gérer les erreurs de parsing JSON
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testGenerateTokenReturnsValidJsonStructure(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/csrfToken');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
        
        $content = $client->getResponse()->getContent();
        self::assertJson($content);
        
        $data = json_decode($content, true);
        self::assertIsArray($data);
        self::assertCount(1, $data);
        self::assertArrayHasKey('csrfToken', $data);
    }

    public function testTokenConsistency(): void
    {
        $client = static::createClient();
        
        // Générer deux tokens dans la même session
        $client->request('GET', '/api/csrfToken');
        $firstTokenResponse = json_decode($client->getResponse()->getContent(), true);
        
        $client->request('GET', '/api/csrfToken');
        $secondTokenResponse = json_decode($client->getResponse()->getContent(), true);
        
        // Les tokens doivent être identiques dans la même session
        self::assertEquals($firstTokenResponse['csrfToken'], $secondTokenResponse['csrfToken']);
    }

    public function testVerifyTokenWithNullValue(): void
    {
        $client = static::createClient();
        
        $client->request(
            'POST',
            '/api/csrfToken/verify',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['csrfToken' => null])
        );

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        self::assertArrayHasKey('error', $responseData);
        self::assertEquals('Invalid CSRF token', $responseData['error']);
    }

    public function testVerifyTokenWithInvalidContentType(): void
    {
        $client = static::createClient();
        
        // Générer un token valide d'abord
        $client->request('GET', '/api/csrfToken');
        $tokenResponse = json_decode($client->getResponse()->getContent(), true);
        $csrfToken = $tokenResponse['csrfToken'];
        
        // Tenter de vérifier avec un mauvais Content-Type
        $client->request(
            'POST',
            '/api/csrfToken/verify',
            [],
            [],
            ['CONTENT_TYPE' => 'text/plain'],
            json_encode(['csrfToken' => $csrfToken])
        );

        // Devrait échouer car le Content-Type n'est pas application/json
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testVerifyTokenWithoutContentType(): void
    {
        $client = static::createClient();
        
        $client->request(
            'POST',
            '/api/csrfToken/verify',
            [],
            [],
            [],
            json_encode(['csrfToken' => 'some-token'])
        );

        // Devrait échouer sans Content-Type approprié
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testVerifyTokenWithAdditionalFields(): void
    {
        $client = static::createClient();
        
        // Générer un token valide
        $client->request('GET', '/api/csrfToken');
        $tokenResponse = json_decode($client->getResponse()->getContent(), true);
        $csrfToken = $tokenResponse['csrfToken'];

        // Vérifier avec des champs supplémentaires
        $client->request(
            'POST',
            '/api/csrfToken/verify',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'csrfToken' => $csrfToken,
                'extraField' => 'value',
                'anotherField' => 123
            ])
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testGenerateTokenResponseFormat(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/csrfToken');

        self::assertResponseIsSuccessful();
        
        $response = $client->getResponse();
        self::assertResponseHeaderSame('content-type', 'application/json');
        
        $content = $response->getContent();
        self::assertNotEmpty($content);
        
        $data = json_decode($content, true);
        self::assertNotNull($data, 'Response should be valid JSON');
        self::assertIsArray($data);
        
        // Vérifier que seul le champ csrfToken est présent
        self::assertArrayHasKey('csrfToken', $data);
        self::assertCount(1, $data);
        
        // Vérifier le format du token
        $token = $data['csrfToken'];
        self::assertIsString($token);
        self::assertNotEmpty($token);
        self::assertGreaterThan(10, strlen($token), 'Token should be long enough');
    }

    public function testTokenUniquenessAcrossDifferentSessions(): void
    {
        // Première session
        $client1 = static::createClient();
        $client1->request('GET', '/api/csrfToken');
        $firstTokenResponse = json_decode($client1->getResponse()->getContent(), true);
        
        // Deuxième session
        $client2 = static::createClient();
        $client2->request('GET', '/api/csrfToken');
        $secondTokenResponse = json_decode($client2->getResponse()->getContent(), true);
        
        // Les tokens doivent être différents entre sessions différentes
        self::assertNotEquals(
            $firstTokenResponse['csrfToken'], 
            $secondTokenResponse['csrfToken'],
            'Tokens should be different across different sessions'
        );
    }

    public function testVerifyTokenCrossSessionValidation(): void
    {
        // Générer un token dans une session
        $client1 = static::createClient();
        $client1->request('GET', '/api/csrfToken');
        $tokenResponse = json_decode($client1->getResponse()->getContent(), true);
        $csrfToken = $tokenResponse['csrfToken'];
        
        // Tenter de vérifier le token dans une autre session
        $client2 = static::createClient();
        $client2->request(
            'POST',
            '/api/csrfToken/verify',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['csrfToken' => $csrfToken])
        );

        // Le token ne devrait pas être valide dans une session différente
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        
        $responseData = json_decode($client2->getResponse()->getContent(), true);
        self::assertArrayHasKey('error', $responseData);
        self::assertEquals('Invalid CSRF token', $responseData['error']);
    }

    public function testVerifyTokenWithVeryLongToken(): void
    {
        $client = static::createClient();
        
        // Créer un token très long (potentielle attaque)
        $longToken = str_repeat('a', 10000);
        
        $client->request(
            'POST',
            '/api/csrfToken/verify',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['csrfToken' => $longToken])
        );

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        self::assertArrayHasKey('error', $responseData);
        self::assertEquals('Invalid CSRF token', $responseData['error']);
    }

    public function testVerifyTokenWithSpecialCharacters(): void
    {
        $client = static::createClient();
        
        // Tester avec des caractères spéciaux
        $specialToken = '<script>alert("xss")</script>';
        
        $client->request(
            'POST',
            '/api/csrfToken/verify',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['csrfToken' => $specialToken])
        );

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        self::assertArrayHasKey('error', $responseData);
        self::assertEquals('Invalid CSRF token', $responseData['error']);
    }

    public function testGenerateTokenMultipleRequestsPerformance(): void
    {
        $client = static::createClient();
        $startTime = microtime(true);
        
        // Effectuer plusieurs requêtes pour tester les performances
        for ($i = 0; $i < 10; $i++) {
            $client->request('GET', '/api/csrfToken');
            self::assertResponseIsSuccessful();
            
            $responseData = json_decode($client->getResponse()->getContent(), true);
            self::assertArrayHasKey('csrfToken', $responseData);
            self::assertNotEmpty($responseData['csrfToken']);
        }
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Vérifier que les requêtes ne prennent pas trop de temps (moins de 2 secondes pour 10 requêtes)
        self::assertLessThan(2.0, $executionTime, 'Token generation should be fast');
    }
}
