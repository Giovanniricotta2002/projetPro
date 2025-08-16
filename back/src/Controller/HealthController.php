<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[OA\Tag(name: 'Health', description: 'Endpoints de vérification de santé')]
class HealthController extends AbstractController
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    #[Route('/api/health', name: 'health_check', methods: ['GET'])]
    #[OA\Get(
        path: '/api/health',
        summary: "Vérifie la santé de l'API",
        description: "Retourne le statut de santé du backend, la base de données, l'environnement et l'espace disque.",
        tags: ['Health'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Statut de santé OK',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'healthy'),
                        new OA\Property(property: 'timestamp', type: 'string', example: '2025-08-11T12:34:56+00:00'),
                        new OA\Property(property: 'service', type: 'string', example: 'MuscuScope Backend'),
                        new OA\Property(property: 'version', type: 'string', example: '1.0.0'),
                        new OA\Property(property: 'database', type: 'string', example: 'connected'),
                        new OA\Property(property: 'environment', type: 'string', example: 'dev'),
                        new OA\Property(
                            property: 'disk_usage',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'free', type: 'integer', example: 123456789),
                                new OA\Property(property: 'total', type: 'integer', example: 987654321),
                                new OA\Property(property: 'percentage_used', type: 'number', format: 'float', example: 12.34),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 503,
                description: 'Base de données inaccessible',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'unhealthy'),
                        new OA\Property(property: 'error', type: 'string', example: 'SQLSTATE[HY000] ...'),
                    ]
                )
            ),
        ]
    )]
    public function healthCheck(): JsonResponse
    {
        $health = [
            'status' => 'healthy',
            'timestamp' => date('c'),
            'service' => 'MuscuScope Backend',
            'version' => '1.0.0',
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
                'percentage_used' => round((($diskTotal - $diskFree) / $diskTotal) * 100, 2),
            ];
        }

        return new JsonResponse($health);
    }

    #[Route('/health', name: 'simple_health_check', methods: ['GET'])]
    #[OA\Get(
        path: '/health',
        summary: 'Vérifie simplement la santé',
        description: 'Retourne un statut simple de santé.',
        tags: ['Health'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Statut simple OK',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'healthy'),
                    ]
                )
            ),
        ]
    )]
    public function simpleHealthCheck(): JsonResponse
    {
        return new JsonResponse(['status' => 'healthy']);
    }
}
