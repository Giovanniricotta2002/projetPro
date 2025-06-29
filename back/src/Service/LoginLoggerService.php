<?php

namespace App\Service;

use App\Entity\LogLogin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class LoginLoggerService
{
    private EntityManagerInterface $entityManager;
    private RequestStack $requestStack;

    public function __construct(
        EntityManagerInterface $entityManager,
        RequestStack $requestStack,
    ) {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
    }

    /**
     * Log une tentative de connexion.
     */
    public function logLoginAttempt(string $login, bool $success, ?\DateTime $date = null): LogLogin
    {
        $request = $this->requestStack->getCurrentRequest();
        $ipAddress = $this->getClientIpAddress($request);

        $logLogin = new LogLogin();
        $logLogin->setLogin($login);
        $logLogin->setSuccess($success);
        $logLogin->setIpPublic($ipAddress);

        if ($date !== null) {
            $logLogin->setDate($date);
        }

        $this->entityManager->persist($logLogin);
        $this->entityManager->flush();

        return $logLogin;
    }

    /**
     * Log une tentative de connexion réussie.
     */
    public function logSuccessfulLogin(string $login): LogLogin
    {
        return $this->logLoginAttempt($login, true);
    }

    /**
     * Log une tentative de connexion échouée.
     */
    public function logFailedLogin(string $login): LogLogin
    {
        return $this->logLoginAttempt($login, false);
    }

    /**
     * Récupère l'adresse IP du client en tenant compte des proxies.
     */
    private function getClientIpAddress($request): string
    {
        if (!$request) {
            return '0.0.0.0';
        }

        // Vérifier si on passe par un proxy/load balancer
        $ipKeys = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);

                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        // Fallback sur l'IP de la requête Symfony
        return $request->getClientIp() ?? '0.0.0.0';
    }

    /**
     * Compte les tentatives de connexion échouées récentes pour une IP.
     */
    public function countRecentFailedAttempts(string $ipAddress, int $minutes = 60): int
    {
        $since = new \DateTime("-{$minutes} minutes");

        return $this->entityManager
            ->getRepository(LogLogin::class)
            ->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->andWhere('l.ipPublic = :ip')
            ->andWhere('l.success = :success')
            ->andWhere('l.date >= :since')
            ->setParameter('ip', $ipAddress)
            ->setParameter('success', false)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte les tentatives de connexion échouées récentes pour un login.
     */
    public function countRecentFailedAttemptsForLogin(string $login, int $minutes = 60): int
    {
        $since = new \DateTime("-{$minutes} minutes");

        return $this->entityManager
            ->getRepository(LogLogin::class)
            ->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->andWhere('l.login = :login')
            ->andWhere('l.success = :success')
            ->andWhere('l.date >= :since')
            ->setParameter('login', $login)
            ->setParameter('success', false)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Vérifie si une IP est temporairement bloquée (trop de tentatives échouées).
     */
    public function isIpBlocked(string $ipAddress, int $maxAttempts = 5, int $blockDurationMinutes = 60): bool
    {
        $failedAttempts = $this->countRecentFailedAttempts($ipAddress, $blockDurationMinutes);

        return $failedAttempts >= $maxAttempts;
    }

    /**
     * Vérifie si un login est temporairement bloqué.
     */
    public function isLoginBlocked(string $login, int $maxAttempts = 3, int $blockDurationMinutes = 30): bool
    {
        $failedAttempts = $this->countRecentFailedAttemptsForLogin($login, $blockDurationMinutes);

        return $failedAttempts >= $maxAttempts;
    }

    /**
     * Obtient des statistiques de connexion pour une période donnée.
     */
    public function getLoginStatistics(\DateTime $from, \DateTime $to): array
    {
        $result = $this->entityManager
            ->getRepository(LogLogin::class)
            ->createQueryBuilder('l')
            ->select('COUNT(l.id) as total')
            ->addSelect('SUM(CASE WHEN l.success = true THEN 1 ELSE 0 END) as successful')
            ->addSelect('SUM(CASE WHEN l.success = false THEN 1 ELSE 0 END) as failed')
            ->andWhere('l.date BETWEEN :from AND :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery()
            ->getSingleResult();

        $total = (int) $result['total'];
        $successful = (int) $result['successful'];
        $failed = (int) $result['failed'];

        return [
            'total' => $total,
            'successful' => $successful,
            'failed' => $failed,
            'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
        ];
    }
}
