<?php

namespace App\Controller;

use App\Service\LoginLoggerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;


#[Route('/api/admin/login-logs', name: 'app_admin_login_logs')]
#[IsGranted('ROLE_ADMIN')]
#[OA\Tag(name: 'Admin Login Logs', description: 'Endpoints d\'administration pour les statistiques et blocages de connexion')]
class LoginStatsController extends AbstractController
{
    public function __construct(
        private readonly LoginLoggerService $loginLogger,
    ) {
    }

    /**
     * Récupère les statistiques de connexion pour une période donnée.
     */
    #[Route('/statistics', name: '_statistics', methods: ['GET'])]
    #[OA\Get(
        path: '/api/admin/login-logs/statistics',
        operationId: 'getLoginStatistics',
        summary: 'Statistiques de connexion',
        description: 'Récupère les statistiques de connexion pour une période donnée (paramètres from/to au format Y-m-d ou Y-m-d H:i:s).',
        tags: ['Admin Login Logs'],
        parameters: [
            new OA\Parameter(name: 'from', in: 'query', required: false, description: 'Date de début', schema: new OA\Schema(type: 'string', example: '2025-08-01')),
            new OA\Parameter(name: 'to', in: 'query', required: false, description: 'Date de fin', schema: new OA\Schema(type: 'string', example: '2025-08-11')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Statistiques retournées',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'period', type: 'object'),
                        new OA\Property(property: 'statistics', type: 'array', items: new OA\Items(type: 'object')),
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Erreur serveur',
            ),
        ]
    )]
    public function getStatistics(Request $request): JsonResponse
    {
        $from = $request->query->get('from');
        $to = $request->query->get('to');

        // Valeurs par défaut : dernière semaine
        $fromDate = $from ? new \DateTime($from) : new \DateTime('-1 week');
        $toDate = $to ? new \DateTime($to) : new \DateTime();

        try {
            $statistics = $this->loginLogger->getLoginStatistics($fromDate, $toDate);

            return $this->json([
                'success' => true,
                'period' => [
                    'from' => $fromDate->format('Y-m-d H:i:s'),
                    'to' => $toDate->format('Y-m-d H:i:s'),
                ],
                'statistics' => $statistics,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Failed to retrieve statistics',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Vérifie le statut de blocage pour une IP.
     */
    #[Route('/check-ip-status/{ip}', name: '_check_ip', methods: ['GET'])]
    #[OA\Get(
        path: '/api/admin/login-logs/check-ip-status/{ip}',
        operationId: 'checkIpStatus',
        summary: 'Vérifier le statut de blocage IP',
        description: 'Vérifie si une IP est actuellement bloquée et retourne le nombre d\'échecs récents.',
        tags: ['Admin Login Logs'],
        parameters: [
            new OA\Parameter(name: 'ip', in: 'path', required: true, description: 'Adresse IP à vérifier', schema: new OA\Schema(type: 'string', example: '192.168.1.1')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Statut IP retourné',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'ip', type: 'string', example: '192.168.1.1'),
                        new OA\Property(property: 'is_blocked', type: 'boolean', example: false),
                        new OA\Property(property: 'recent_failed_attempts', type: 'integer', example: 2),
                        new OA\Property(property: 'status', type: 'string', example: 'allowed'),
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Erreur serveur',
            ),
        ]
    )]
    public function checkIpStatus(string $ip): JsonResponse
    {
        try {
            $isBlocked = $this->loginLogger->isIpBlocked($ip);
            $recentFailures = $this->loginLogger->countRecentFailedAttempts($ip);

            return $this->json([
                'success' => true,
                'ip' => $ip,
                'is_blocked' => $isBlocked,
                'recent_failed_attempts' => $recentFailures,
                'status' => $isBlocked ? 'blocked' : 'allowed',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Failed to check IP status',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Vérifie le statut de blocage pour un login.
     */
    #[Route('/check-login-status/{login}', name: '_check_login', methods: ['GET'])]
    #[OA\Get(
        path: '/api/admin/login-logs/check-login-status/{login}',
        operationId: 'checkLoginStatus',
        summary: 'Vérifier le statut de blocage login',
        description: 'Vérifie si un login est actuellement bloqué et retourne le nombre d\'échecs récents.',
        tags: ['Admin Login Logs'],
        parameters: [
            new OA\Parameter(name: 'login', in: 'path', required: true, description: 'Login à vérifier', schema: new OA\Schema(type: 'string', example: 'john.doe')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Statut login retourné',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'login', type: 'string', example: 'john.doe'),
                        new OA\Property(property: 'is_blocked', type: 'boolean', example: false),
                        new OA\Property(property: 'recent_failed_attempts', type: 'integer', example: 1),
                        new OA\Property(property: 'status', type: 'string', example: 'allowed'),
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Erreur serveur',
            ),
        ]
    )]
    public function checkLoginStatus(string $login): JsonResponse
    {
        try {
            $isBlocked = $this->loginLogger->isLoginBlocked($login);
            $recentFailures = $this->loginLogger->countRecentFailedAttemptsForLogin($login);

            return $this->json([
                'success' => true,
                'login' => $login,
                'is_blocked' => $isBlocked,
                'recent_failed_attempts' => $recentFailures,
                'status' => $isBlocked ? 'blocked' : 'allowed',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Failed to check login status',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Endpoint pour obtenir des statistiques en temps réel.
     */
    #[Route('/real-time-stats', name: '_realtime', methods: ['GET'])]
    #[OA\Get(
        path: '/api/admin/login-logs/real-time-stats',
        operationId: 'getRealTimeLoginStats',
        summary: 'Statistiques de connexion en temps réel',
        description: 'Retourne les statistiques de connexion pour la dernière heure, 24h et semaine.',
        tags: ['Admin Login Logs'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Statistiques temps réel retournées',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'timestamp', type: 'string', example: '2025-08-11 14:00:00'),
                        new OA\Property(property: 'statistics', type: 'object'),
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Erreur serveur',
            ),
        ]
    )]
    public function getRealTimeStats(): JsonResponse
    {
        try {
            $now = new \DateTime();
            $oneHourAgo = new \DateTime('-1 hour');
            $oneDayAgo = new \DateTime('-24 hours');
            $oneWeekAgo = new \DateTime('-1 week');

            $hourlyStats = $this->loginLogger->getLoginStatistics($oneHourAgo, $now);
            $dailyStats = $this->loginLogger->getLoginStatistics($oneDayAgo, $now);
            $weeklyStats = $this->loginLogger->getLoginStatistics($oneWeekAgo, $now);

            return $this->json([
                'success' => true,
                'timestamp' => $now->format('Y-m-d H:i:s'),
                'statistics' => [
                    'last_hour' => $hourlyStats,
                    'last_24_hours' => $dailyStats,
                    'last_week' => $weeklyStats,
                ],
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Failed to retrieve real-time statistics',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Endpoint pour réinitialiser les tentatives échouées d'une IP (déblocage manuel).
     */
    #[Route('/unblock-ip/{ip}', name: '_unblock_ip', methods: ['POST'])]
    #[OA\Post(
        path: '/api/admin/login-logs/unblock-ip/{ip}',
        operationId: 'unblockIp',
        summary: 'Débloquer une IP',
        description: 'Réinitialise les tentatives échouées pour une IP (fonctionnalité non implémentée).',
        tags: ['Admin Login Logs'],
        parameters: [
            new OA\Parameter(name: 'ip', in: 'path', required: true, description: 'Adresse IP à débloquer', schema: new OA\Schema(type: 'string', example: '192.168.1.1')),
        ],
        responses: [
            new OA\Response(
                response: 501,
                description: 'Fonctionnalité non implémentée',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'IP unblocking feature not yet implemented'),
                        new OA\Property(property: 'suggestion', type: 'string', example: 'This would require additional database logic to clear recent failed attempts'),
                    ]
                )
            ),
        ]
    )]
    public function unblockIp(string $ip): JsonResponse
    {
        // Note: Cette fonctionnalité nécessiterait une méthode dans le service
        // pour marquer les tentatives comme "pardonnées" ou les supprimer

        return $this->json([
            'success' => false,
            'message' => 'IP unblocking feature not yet implemented',
            'suggestion' => 'This would require additional database logic to clear recent failed attempts',
        ], Response::HTTP_NOT_IMPLEMENTED);
    }

    /**
     * Endpoint pour réinitialiser les tentatives échouées d'un login.
     */
    #[Route('/unblock-login/{login}', name: '_unblock_login', methods: ['POST'])]
    #[OA\Post(
        path: '/api/admin/login-logs/unblock-login/{login}',
        operationId: 'unblockLogin',
        summary: 'Débloquer un login',
        description: 'Réinitialise les tentatives échouées pour un login (fonctionnalité non implémentée).',
        tags: ['Admin Login Logs'],
        parameters: [
            new OA\Parameter(name: 'login', in: 'path', required: true, description: 'Login à débloquer', schema: new OA\Schema(type: 'string', example: 'john.doe')),
        ],
        responses: [
            new OA\Response(
                response: 501,
                description: 'Fonctionnalité non implémentée',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Login unblocking feature not yet implemented'),
                        new OA\Property(property: 'suggestion', type: 'string', example: 'This would require additional database logic to clear recent failed attempts'),
                    ]
                )
            ),
        ]
    )]
    public function unblockLogin(string $login): JsonResponse
    {
        // Note: Cette fonctionnalité nécessiterait une méthode dans le service
        // pour marquer les tentatives comme "pardonnées" ou les supprimer

        return $this->json([
            'success' => false,
            'message' => 'Login unblocking feature not yet implemented',
            'suggestion' => 'This would require additional database logic to clear recent failed attempts',
        ], Response::HTTP_NOT_IMPLEMENTED);
    }
}
