<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Connection;

class HealthController extends AbstractController
{
    public function __construct(
        private Connection $connection
    ) {}

    #[Route('/api/health', name: 'health_check', methods: ['GET'])]
    public function healthCheck(): JsonResponse
    {
        $health = [
            'status' => 'healthy',
            'timestamp' => date('c'),
            'service' => 'MuscuScope Backend',
            'version' => '1.0.0'
        ];

        // Vérifier la connexion à la base de données
        try {
            $this->connection->executeQuery('SELECT 1');
            $health['database'] = 'connected';
        } catch (\Exception $e) {
            $health['database'] = 'disconnected';
            $health['status'] = 'unhealthy';
            $health['error'] = $e->getMessage();
            
            return new JsonResponse($health, 503);
        }

        // Vérifier l'environnement
        $health['environment'] = $_ENV['APP_ENV'] ?? 'unknown';
        
        // Vérifier l'espace disque (optionnel)
        $diskFree = disk_free_space(__DIR__);
        $diskTotal = disk_total_space(__DIR__);
        if ($diskFree && $diskTotal) {
            $health['disk_usage'] = [
                'free' => $diskFree,
                'total' => $diskTotal,
                'percentage_used' => round((($diskTotal - $diskFree) / $diskTotal) * 100, 2)
            ];
        }

        return new JsonResponse($health);
    }

    #[Route('/health', name: 'simple_health_check', methods: ['GET'])]
    public function simpleHealthCheck(): JsonResponse
    {
        return new JsonResponse(['status' => 'healthy']);
    }
}
