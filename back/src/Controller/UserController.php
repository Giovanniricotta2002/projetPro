<?php

namespace App\Controller;

use App\DTO\ErrorResponseDTO;
use App\DTO\LoginUserDTO;
use App\Entity\Utilisateur;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'app_api')]
#[OA\Tag(name: 'User', description: 'Gestion des utilisateurs')]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Entrez votre token JWT au format: Bearer {votre_token}'
)]
final class UserController extends AbstractController
{
    /**
     * Récupère les informations de l'utilisateur authentifié
     * 
     * Cette méthode retourne les informations de profil de l'utilisateur actuellement connecté,
     * incluant son ID, nom d'utilisateur, rôles et date de dernière visite.
     */
    #[Route('/me', name: '_me', methods: ['GET'])]
    #[OA\Get(
        path: '/api/me',
        summary: 'Récupérer les informations de l\'utilisateur actuel',
        description: 'Retourne les informations de profil de l\'utilisateur authentifié',
        tags: ['User']
    )]
    #[OA\Response(
        response: 200,
        description: 'Informations utilisateur récupérées avec succès',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer', description: 'Identifiant unique de l\'utilisateur', example: 1),
                new OA\Property(property: 'username', type: 'string', description: 'Nom d\'utilisateur', example: 'john_doe'),
                new OA\Property(property: 'roles', type: 'array', description: 'Liste des rôles de l\'utilisateur', items: new OA\Items(type: 'string'), example: ['ROLE_USER']),
                new OA\Property(property: 'lastVisit', type: 'string', format: 'date-time', description: 'Date et heure de la dernière visite', example: '2025-07-01 10:30:00')
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Utilisateur non authentifié',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'error', type: 'string', description: 'Code d\'erreur', example: 'UNAUTHORIZED'),
                new OA\Property(property: 'message', type: 'string', description: 'Message d\'erreur', example: 'User not authenticated')
            ]
        )
    )]
    public function me(Request $request): Response
    {
        return new JsonResponse([$request->cookies->all(), $this->getUser()]);
    //     return new JsonResponse([$request->cookies->all(), $this->getUser()
    // ], Response::HTTP_OK, [
    //         'Access-Control-Allow-Origin' => '*',
    //         'Access-Control-Allow-Methods' => 'POST, OPTIONS',
    //         'Access-Control-Allow-Headers' => 'Content-Type, X-CSRF-Token',
    //     ]);

        // Récupération de l'utilisateur authentifié via le token JWT
        /**@var Utilisateur */
        $user = $this->getUser();

        // Vérification que l'utilisateur est bien authentifié
        if (!$user) {
            $errorDto = ErrorResponseDTO::withMessage('UNAUTHORIZED', 'User not authenticated');
            return $this->json($errorDto->toArray(), Response::HTTP_UNAUTHORIZED);
        }

        // Création du DTO avec les informations utilisateur
        $uderDto = new LoginUserDTO(
            id: $user->getId(),
            username: $user->getUsername(),
            roles: $user->getRoles(),
            lastVisit: $user->getLastVisit()->format('Y-m-d H:i:s'),
        );

        // Retour des données utilisateur en JSON
        return $this->json($uderDto->toArray(), Response::HTTP_OK);
    }

    #[Route('/me', name: '_me_options', methods: ['OPTIONS'])]
    public function meOptions(Request $request): Response
    {
        return new Response('', Response::HTTP_OK, [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'POST, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, X-CSRF-Token',
        ]);
    }
}
