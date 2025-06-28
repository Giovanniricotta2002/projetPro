<?php

namespace App\Tests\Entity;

use App\Entity\LogLogin;
use PHPUnit\Framework\TestCase;

class LogLoginTest extends TestCase
{
    private LogLogin $logLogin;

    protected function setUp(): void
    {
        $this->logLogin = new LogLogin();
    }

    public function testConstructor(): void
    {
        // Vérifier que les valeurs par défaut sont correctement définies
        self::assertInstanceOf(\DateTime::class, $this->logLogin->getDate());
        self::assertNull($this->logLogin->getId());
        self::assertNull($this->logLogin->getLogin());
        self::assertNull($this->logLogin->getIpPublic());
        self::assertNull($this->logLogin->isSuccess());
    }

    public function testIdGetter(): void
    {
        // L'ID devrait être null avant la persistance
        self::assertNull($this->logLogin->getId());
    }

    public function testDateGetter(): void
    {
        // La date est définie dans le constructeur
        self::assertInstanceOf(\DateTime::class, $this->logLogin->getDate());
        
        $now = new \DateTime();
        self::assertLessThanOrEqual($now, $this->logLogin->getDate());
    }

    public function testDateGetterAndSetter(): void
    {
        $date = new \DateTime('2024-01-01 10:00:00');
        
        $result = $this->logLogin->setDate($date);
        self::assertEquals($date, $this->logLogin->getDate());
        self::assertInstanceOf(LogLogin::class, $result); // Test fluent interface
    }

    public function testDateWithTimezone(): void
    {
        // Test avec différents fuseaux horaires (DATETIMETZ_MUTABLE)
        $parisDate = new \DateTime('2024-01-01 10:00:00', new \DateTimeZone('Europe/Paris'));
        $utcDate = new \DateTime('2024-01-01 10:00:00', new \DateTimeZone('UTC'));
        $tokyoDate = new \DateTime('2024-01-01 10:00:00', new \DateTimeZone('Asia/Tokyo'));
        
        $this->logLogin->setDate($parisDate);
        self::assertEquals($parisDate, $this->logLogin->getDate());
        self::assertEquals('Europe/Paris', $this->logLogin->getDate()->getTimezone()->getName());
        
        $this->logLogin->setDate($utcDate);
        self::assertEquals($utcDate, $this->logLogin->getDate());
        
        $this->logLogin->setDate($tokyoDate);
        self::assertEquals($tokyoDate, $this->logLogin->getDate());
    }

    public function testLoginGetterAndSetter(): void
    {
        $login = 'user123';
        
        self::assertNull($this->logLogin->getLogin());
        
        $result = $this->logLogin->setLogin($login);
        self::assertEquals($login, $this->logLogin->getLogin());
        self::assertInstanceOf(LogLogin::class, $result);
    }

    public function testLoginLength(): void
    {
        $validLogin = 'testuser'; // Moins de 30 caractères
        $longLogin = str_repeat('a', 35); // Plus de 30 caractères
        
        $this->logLogin->setLogin($validLogin);
        self::assertEquals($validLogin, $this->logLogin->getLogin());
        
        $this->logLogin->setLogin($longLogin);
        self::assertEquals($longLogin, $this->logLogin->getLogin());
    }

    public function testLoginFormats(): void
    {
        // Test avec différents formats de login
        $logins = [
            'user123',
            'user.name',
            'user_name',
            'user-name',
            'user@example.com',
            'User123',
            '123user'
        ];
        
        foreach ($logins as $login) {
            $this->logLogin->setLogin($login);
            self::assertEquals($login, $this->logLogin->getLogin());
        }
    }

    public function testIpPublicGetterAndSetter(): void
    {
        $ip = '192.168.1.1';
        
        self::assertNull($this->logLogin->getIpPublic());
        
        $result = $this->logLogin->setIpPublic($ip);
        self::assertEquals($ip, $this->logLogin->getIpPublic());
        self::assertInstanceOf(LogLogin::class, $result);
    }

    public function testIpPublicLength(): void
    {
        $validIp = '192.168.1.1'; // Moins de 20 caractères
        $longIp = '192.168.1.255.192.168.1.255'; // Plus de 20 caractères
        
        $this->logLogin->setIpPublic($validIp);
        self::assertEquals($validIp, $this->logLogin->getIpPublic());
        
        $this->logLogin->setIpPublic($longIp);
        self::assertEquals($longIp, $this->logLogin->getIpPublic());
    }

    public function testIpPublicFormats(): void
    {
        // Test avec différents formats d'IP
        $ips = [
            '127.0.0.1',           // localhost
            '192.168.1.1',         // IP privée
            '10.0.0.1',            // IP privée
            '172.16.0.1',          // IP privée
            '8.8.8.8',             // IP publique (Google DNS)
            '255.255.255.255',     // IP broadcast
            '0.0.0.0',             // IP nulle
            '203.0.113.1',         // IP de test
            '2001:db8::1',         // IPv6 (si supporté)
            '::1'                  // IPv6 localhost
        ];
        
        foreach ($ips as $ip) {
            $this->logLogin->setIpPublic($ip);
            self::assertEquals($ip, $this->logLogin->getIpPublic());
        }
    }

    public function testSuccessGetterAndSetter(): void
    {
        self::assertNull($this->logLogin->isSuccess());
        
        $result = $this->logLogin->setSuccess(true);
        self::assertTrue($this->logLogin->isSuccess());
        self::assertInstanceOf(LogLogin::class, $result);
        
        $this->logLogin->setSuccess(false);
        self::assertFalse($this->logLogin->isSuccess());
    }

    public function testFluentInterface(): void
    {
        // Test que toutes les méthodes setter retournent l'instance
        $date = new \DateTime();
        $login = 'testuser';
        $ip = '192.168.1.100';
        $success = true;
        
        $result = $this->logLogin
            ->setDate($date)
            ->setLogin($login)
            ->setIpPublic($ip)
            ->setSuccess($success);
        
        self::assertInstanceOf(LogLogin::class, $result);
        self::assertEquals($date, $this->logLogin->getDate());
        self::assertEquals($login, $this->logLogin->getLogin());
        self::assertEquals($ip, $this->logLogin->getIpPublic());
        self::assertTrue($this->logLogin->isSuccess());
    }

    public function testCompleteLogLogin(): void
    {
        // Test d'un log de connexion complet
        $date = new \DateTime('2024-01-01 10:30:00');
        $login = 'admin';
        $ip = '203.0.113.42';
        $success = true;
        
        $this->logLogin
            ->setDate($date)
            ->setLogin($login)
            ->setIpPublic($ip)
            ->setSuccess($success);
        
        // Vérifications
        self::assertEquals($date, $this->logLogin->getDate());
        self::assertEquals($login, $this->logLogin->getLogin());
        self::assertEquals($ip, $this->logLogin->getIpPublic());
        self::assertTrue($this->logLogin->isSuccess());
    }

    public function testFailedLoginLog(): void
    {
        // Test d'un log de connexion échouée
        $this->logLogin
            ->setLogin('hacker')
            ->setIpPublic('10.0.0.666')
            ->setSuccess(false);
        
        self::assertEquals('hacker', $this->logLogin->getLogin());
        self::assertEquals('10.0.0.666', $this->logLogin->getIpPublic());
        self::assertFalse($this->logLogin->isSuccess());
    }

    public function testSuccessfulLoginLog(): void
    {
        // Test d'un log de connexion réussie
        $this->logLogin
            ->setLogin('validuser')
            ->setIpPublic('192.168.1.50')
            ->setSuccess(true);
        
        self::assertEquals('validuser', $this->logLogin->getLogin());
        self::assertEquals('192.168.1.50', $this->logLogin->getIpPublic());
        self::assertTrue($this->logLogin->isSuccess());
    }

    public function testSecurityScenarios(): void
    {
        // Test de différents scénarios de sécurité
        $scenarios = [
            ['login' => 'admin', 'ip' => '127.0.0.1', 'success' => true],      // Admin local
            ['login' => 'user', 'ip' => '192.168.1.10', 'success' => true],    // Utilisateur interne
            ['login' => 'guest', 'ip' => '8.8.8.8', 'success' => false],       // Invité externe (échec)
            ['login' => 'root', 'ip' => '203.0.113.1', 'success' => false],    // Tentative root externe
            ['login' => '', 'ip' => '0.0.0.0', 'success' => false],            // Login vide
        ];
        
        foreach ($scenarios as $scenario) {
            $logLogin = new LogLogin();
            $logLogin
                ->setLogin($scenario['login'])
                ->setIpPublic($scenario['ip'])
                ->setSuccess($scenario['success']);
            
            self::assertEquals($scenario['login'], $logLogin->getLogin());
            self::assertEquals($scenario['ip'], $logLogin->getIpPublic());
            self::assertEquals($scenario['success'], $logLogin->isSuccess());
        }
    }

    public function testDateConsistency(): void
    {
        // Vérifier que la date dans le constructeur est récente
        $beforeCreation = new \DateTime();
        $logLogin = new LogLogin();
        $afterCreation = new \DateTime();
        
        self::assertGreaterThanOrEqual($beforeCreation, $logLogin->getDate());
        self::assertLessThanOrEqual($afterCreation, $logLogin->getDate());
    }

    public function testSpecialCharactersInLogin(): void
    {
        // Test avec des caractères spéciaux dans le login
        $specialLogins = [
            'user@domain.com',
            'user.name+tag',
            'user-name_123',
            'user\'s account',
            'user"name',
            'user&name',
            'user<script>',
            'user%20name'
        ];
        
        foreach ($specialLogins as $login) {
            $this->logLogin->setLogin($login);
            self::assertEquals($login, $this->logLogin->getLogin());
        }
    }

    public function testMaliciousContent(): void
    {
        // Test avec du contenu potentiellement malicieux
        $maliciousLogin = "'; DROP TABLE log_login; --";
        $maliciousIp = "<script>alert('xss')</script>";
        
        $this->logLogin->setLogin($maliciousLogin);
        $this->logLogin->setIpPublic($maliciousIp);
        
        // Les valeurs devraient être stockées telles quelles
        self::assertEquals($maliciousLogin, $this->logLogin->getLogin());
        self::assertEquals($maliciousIp, $this->logLogin->getIpPublic());
    }

    public function testEmptyValues(): void
    {
        // Test avec des valeurs vides
        $this->logLogin->setLogin('');
        $this->logLogin->setIpPublic('');
        
        self::assertEquals('', $this->logLogin->getLogin());
        self::assertEquals('', $this->logLogin->getIpPublic());
    }

    public function testBruteForceScenario(): void
    {
        // Simuler des tentatives de force brute
        $attempts = [
            ['admin', '192.168.1.1', false],
            ['admin', '192.168.1.1', false],
            ['admin', '192.168.1.1', false],
            ['admin', '192.168.1.1', true],  // Finalement réussie
        ];
        
        $logs = [];
        foreach ($attempts as [$login, $ip, $success]) {
            $log = new LogLogin();
            $log->setLogin($login)
                ->setIpPublic($ip)
                ->setSuccess($success);
            $logs[] = $log;
        }
        
        // Vérifier que les 3 premiers sont des échecs
        for ($i = 0; $i < 3; $i++) {
            self::assertFalse($logs[$i]->isSuccess());
            self::assertEquals('admin', $logs[$i]->getLogin());
            self::assertEquals('192.168.1.1', $logs[$i]->getIpPublic());
        }
        
        // Le dernier devrait être un succès
        self::assertTrue($logs[3]->isSuccess());
    }

    public function testToString(): void
    {
        $login = 'testuser';
        $this->logLogin->setLogin($login);
        
        // Si l'entité a une méthode __toString(), la tester
        if (method_exists($this->logLogin, '__toString')) {
            self::assertIsString((string) $this->logLogin);
            self::assertStringContainsString($login, (string) $this->logLogin);
        } else {
            self::markTestSkipped('Méthode __toString() non implémentée');
        }
    }
}
