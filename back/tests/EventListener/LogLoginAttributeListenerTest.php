<?php

namespace App\Tests\EventListener;

use App\Attribute\LogLogin;
use App\EventListener\LogLoginAttributeListener;
use App\Service\LoginLoggerService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\HttpKernel\Event\{ControllerEvent, ResponseEvent};
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Tests unitaires pour LogLoginAttributeListener.
 *
 * Teste le comportement de l'event listener qui gère automatiquement
 * le logging des connexions basé sur l'attribut LogLogin.
 */
class LogLoginAttributeListenerTest extends TestCase
{
    private MockObject|LoginLoggerService $loginLoggerMock;
    private LogLoginAttributeListener $listener;

    protected function setUp(): void
    {
        $this->loginLoggerMock = $this->createMock(LoginLoggerService::class);
        $this->listener = new LogLoginAttributeListener($this->loginLoggerMock);
    }

    /**
     * Teste que l'event listener ignore les contrôleurs non-array.
     */
    public function testIgnoresNonArrayControllers(): void
    {
        $request = new Request();
        $kernel = $this->createMock(HttpKernelInterface::class);

        $event = new ControllerEvent(
            $kernel,
            fn () => new Response(), // Callable au lieu d'array
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        // Ne devrait pas lever d'exception
        $this->listener->onController($event);

        // Le contrôleur ne devrait pas avoir changé
        $this->assertIsCallable($event->getController());
    }

    /**
     * Teste que l'event listener ignore les contrôleurs sans attribut LogLogin.
     */
    public function testIgnoresControllersWithoutLogLoginAttribute(): void
    {
        $controller = new class {
            public function action()
            {
                return new Response();
            }
        };

        $request = new Request();
        $kernel = $this->createMock(HttpKernelInterface::class);

        $event = new ControllerEvent(
            $kernel,
            [$controller, 'action'],
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $this->listener->onController($event);

        // Le contrôleur ne devrait pas avoir changé
        $this->assertEquals([$controller, 'action'], $event->getController());
    }

    /**
     * Teste que l'event listener ignore les attributs désactivés.
     */
    public function testIgnoresDisabledAttributes(): void
    {
        $controller = new class {
            #[LogLogin(enabled: false)]
            public function action()
            {
                return new Response();
            }
        };

        $request = Request::create('/api/login', 'POST', [], [], [], [], '{"login":"test","password":"test"}');
        $kernel = $this->createMock(HttpKernelInterface::class);

        $event = new ControllerEvent(
            $kernel,
            [$controller, 'action'],
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $this->listener->onController($event);

        // Le contrôleur ne devrait pas avoir changé
        $this->assertEquals([$controller, 'action'], $event->getController());
    }

    /**
     * Teste que l'event listener ignore les requêtes non-POST.
     */
    public function testIgnoresNonPostRequests(): void
    {
        $controller = new class {
            #[LogLogin]
            public function action()
            {
                return new Response();
            }
        };

        $request = Request::create('/api/login', 'GET');
        $kernel = $this->createMock(HttpKernelInterface::class);

        $event = new ControllerEvent(
            $kernel,
            [$controller, 'action'],
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $this->listener->onController($event);

        // Le contrôleur ne devrait pas avoir changé
        $this->assertEquals([$controller, 'action'], $event->getController());
    }

    /**
     * Teste que l'event listener ignore les requêtes sans contenu.
     */
    public function testIgnoresRequestsWithoutContent(): void
    {
        $controller = new class {
            #[LogLogin]
            public function action()
            {
                return new Response();
            }
        };

        $request = Request::create('/api/login', 'POST');
        $kernel = $this->createMock(HttpKernelInterface::class);

        $event = new ControllerEvent(
            $kernel,
            [$controller, 'action'],
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $this->listener->onController($event);

        // Le contrôleur ne devrait pas avoir changé
        $this->assertEquals([$controller, 'action'], $event->getController());
    }

    /**
     * Teste le blocage par IP.
     */
    public function testIpBlocking(): void
    {
        $controller = new class {
            #[LogLogin(maxIpAttempts: 3, ipBlockDuration: 60)]
            public function action()
            {
                return new Response();
            }
        };

        $request = Request::create(
            '/api/login',
            'POST',
            [],
            [],
            [],
            ['REMOTE_ADDR' => '192.168.1.1'],
            '{"login":"test","password":"test"}'
        );
        $kernel = $this->createMock(HttpKernelInterface::class);

        // Mock: IP est bloquée
        $this->loginLoggerMock
            ->expects($this->once())
            ->method('isIpBlocked')
            ->with('192.168.1.1', 3, 60)
            ->willReturn(true);

        $this->loginLoggerMock
            ->expects($this->once())
            ->method('logFailedLogin')
            ->with('test');

        $event = new ControllerEvent(
            $kernel,
            [$controller, 'action'],
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $this->listener->onController($event);

        // Le contrôleur devrait avoir été remplacé par une réponse d'erreur
        $this->assertIsCallable($event->getController());

        $response = call_user_func($event->getController());
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_TOO_MANY_REQUESTS, $response->getStatusCode());
    }

    /**
     * Teste le blocage par login.
     */
    public function testLoginBlocking(): void
    {
        $controller = new class {
            #[LogLogin(maxLoginAttempts: 2, loginBlockDuration: 30)]
            public function action()
            {
                return new Response();
            }
        };

        $request = Request::create(
            '/api/login',
            'POST',
            [],
            [],
            [],
            ['REMOTE_ADDR' => '192.168.1.1'],
            '{"login":"blockeduser","password":"test"}'
        );
        $kernel = $this->createMock(HttpKernelInterface::class);

        // Mock: IP n'est pas bloquée, mais login oui
        $this->loginLoggerMock
            ->expects($this->once())
            ->method('isIpBlocked')
            ->willReturn(false);

        $this->loginLoggerMock
            ->expects($this->once())
            ->method('isLoginBlocked')
            ->with('blockeduser', 2, 30)
            ->willReturn(true);

        $this->loginLoggerMock
            ->expects($this->once())
            ->method('logFailedLogin')
            ->with('blockeduser');

        $event = new ControllerEvent(
            $kernel,
            [$controller, 'action'],
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $this->listener->onController($event);

        $response = call_user_func($event->getController());
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_TOO_MANY_REQUESTS, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('retry_after', $data);
        $this->assertEquals(1800, $data['retry_after']); // 30 minutes * 60
    }

    /**
     * Teste le stockage des informations pour le logging post-réponse.
     */
    public function testStorePendingLogInfo(): void
    {
        $controller = new class {
            #[LogLogin]
            public function action()
            {
                return new Response();
            }
        };

        $request = Request::create(
            '/api/login',
            'POST',
            [],
            [],
            [],
            ['REMOTE_ADDR' => '192.168.1.1'],
            '{"login":"validuser","password":"validpass"}'
        );
        $kernel = $this->createMock(HttpKernelInterface::class);

        // Mock: Aucun blocage
        $this->loginLoggerMock
            ->expects($this->once())
            ->method('isIpBlocked')
            ->willReturn(false);

        $this->loginLoggerMock
            ->expects($this->once())
            ->method('isLoginBlocked')
            ->willReturn(false);

        $event = new ControllerEvent(
            $kernel,
            [$controller, 'action'],
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $this->listener->onController($event);

        // Le contrôleur ne devrait pas avoir changé
        $this->assertEquals([$controller, 'action'], $event->getController());
    }

    /**
     * Teste le logging après une réponse réussie.
     */
    public function testSuccessfulResponseLogging(): void
    {
        // D'abord, simuler l'événement controller
        $controller = new class {
            #[LogLogin]
            public function action()
            {
                return new Response();
            }
        };

        $request = Request::create(
            '/api/login',
            'POST',
            [],
            [],
            [],
            ['REMOTE_ADDR' => '192.168.1.1'],
            '{"login":"user","password":"pass"}'
        );
        $kernel = $this->createMock(HttpKernelInterface::class);

        $this->loginLoggerMock->method('isIpBlocked')->willReturn(false);
        $this->loginLoggerMock->method('isLoginBlocked')->willReturn(false);

        $controllerEvent = new ControllerEvent(
            $kernel,
            [$controller, 'action'],
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        // Traiter l'événement controller
        $this->listener->onController($controllerEvent);

        // Maintenant, simuler l'événement response
        $response = new JsonResponse(['success' => true], Response::HTTP_OK);
        $responseEvent = new ResponseEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response
        );

        // Mock: Attendre l'appel de logging
        $this->loginLoggerMock
            ->expects($this->once())
            ->method('logLoginAttempt')
            ->with('user', true, $this->isInstanceOf(\DateTime::class));

        $this->listener->onResponse($responseEvent);

        // Vérifier les headers ajoutés
        $this->assertEquals('true', $response->headers->get('X-Login-Logged'));
        $this->assertEquals('success', $response->headers->get('X-Login-Status'));
    }

    /**
     * Teste le logging après une réponse d'échec.
     */
    public function testFailureResponseLogging(): void
    {
        // Simuler l'événement controller
        $controller = new class {
            #[LogLogin]
            public function action()
            {
                return new Response();
            }
        };

        $request = Request::create(
            '/api/login',
            'POST',
            [],
            [],
            [],
            ['REMOTE_ADDR' => '192.168.1.1'],
            '{"login":"user","password":"wrongpass"}'
        );
        $kernel = $this->createMock(HttpKernelInterface::class);

        $this->loginLoggerMock->method('isIpBlocked')->willReturn(false);
        $this->loginLoggerMock->method('isLoginBlocked')->willReturn(false);

        $controllerEvent = new ControllerEvent(
            $kernel,
            [$controller, 'action'],
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $this->listener->onController($controllerEvent);

        // Réponse d'échec
        $response = new JsonResponse(['error' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        $responseEvent = new ResponseEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response
        );

        // Mock: Attendre l'appel de logging pour échec
        $this->loginLoggerMock
            ->expects($this->once())
            ->method('logLoginAttempt')
            ->with('user', false, $this->isInstanceOf(\DateTime::class));

        $this->listener->onResponse($responseEvent);

        $this->assertEquals('true', $response->headers->get('X-Login-Logged'));
        $this->assertEquals('failure', $response->headers->get('X-Login-Status'));
    }

    /**
     * Teste la gestion des erreurs JSON malformées.
     */
    public function testHandlesMalformedJson(): void
    {
        $controller = new class {
            #[LogLogin]
            public function action()
            {
                return new Response();
            }
        };

        $request = Request::create(
            '/api/login',
            'POST',
            [],
            [],
            [],
            [],
            '{"login":"test"password":"invalid json}'
        );
        $kernel = $this->createMock(HttpKernelInterface::class);

        $event = new ControllerEvent(
            $kernel,
            [$controller, 'action'],
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        // Ne devrait pas lever d'exception
        $this->listener->onController($event);

        // Le contrôleur ne devrait pas avoir changé
        $this->assertEquals([$controller, 'action'], $event->getController());
    }
}
