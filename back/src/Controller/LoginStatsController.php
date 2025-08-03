<?php

namespace App\Controller;

use App\Service\LoginLoggerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin/login-logs', name: 'app_admin_login_logs')]
#[IsGranted('ROLE_ADMIN')]
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
