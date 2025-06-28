<?php

namespace App\Controller;

use App\Attribute\LogLogin;
use App\DTO\ErrorResponseDTO;
use App\DTO\LoginResponseDTO;
use App\DTO\LoginSuccessResponseDTO;
use App\DTO\LoginUserDTO;
use App\Services\InitSerializerService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Serializer\Serializer;
use App\Repository\UtilisateurRepository;

#[Route('/api/login', name: 'app_login')]
#[OA\Tag(name: 'Authentication', description: 'Gestion de l\'authentification utilisateur')]
final class LoginController extends AbstractController
{
    private Serializer $serializer;

    public function __construct(
        private readonly UtilisateurRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface $entityManager
    ) {
        $init = new InitSerializerService();
        $this->serializer = $init->serializer;
    }


    #[Route('', name: '_log', methods: ['POST', 'OPTIONS'])]
    #[IsCsrfTokenValid('authenticate', tokenKey: 'X-CSRF-Token', methods: ['POST'])]
    #[LogLogin(
        enabled: true,
        usernameField: 'login',
        passwordField: 'password',
        checkBlocking: true,
        maxIpAttempts: 5,
        maxLoginAttempts: 3,
        ipBlockDuration: 60,
        loginBlockDuration: 30
    )]
    #[OA\Post(
        path: '/api/login',
        operationId: 'authenticateUser',
        summary: 'Authentifier un utilisateur',
        description: 'Authentifie un utilisateur avec son login et mot de passe. Inclut une protection contre les attaques par force brute avec blocage automatique par IP et par login.',
        tags: ['Authentication']
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Identifiants de connexion',
        content: new OA\JsonContent(
            type: 'object',
            required: ['login', 'password'],
            properties: [
                new OA\Property(
                    property: 'login',
                    type: 'string',
                    description: 'Nom d\'utilisateur ou email',
                    example: 'john.doe'
                ),
                new OA\Property(
                    property: 'password',
                    type: 'string',
                    description: 'Mot de passe de l\'utilisateur',
                    example: 'motDePasseSecret123'
                )
            ]
        )
    )]
    #[OA\Parameter(
        name: 'X-CSRF-Token',
        description: 'Token CSRF pour la sécurité',
        in: 'header',
        required: true,
        schema: new OA\Schema(type: 'string', example: 'abc123def456ghi789')
    )]
    #[OA\Response(
        response: 200,
        description: 'Connexion réussie',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Login successful'),
                new OA\Property(
                    property: 'user',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 123),
                        new OA\Property(property: 'username', type: 'string', example: 'john.doe'),
                        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['ROLE_USER']),
                        new OA\Property(property: 'last_visit', type: 'string', format: 'date-time', example: '2025-01-15 14:30:00')
                    ]
                )
            ]
        ),
        headers: [
            new OA\Header(header: 'X-Login-Logged', description: 'Indique si la tentative a été loggée', schema: new OA\Schema(type: 'string', example: 'true')),
            new OA\Header(header: 'X-Login-Status', description: 'Statut de la connexion', schema: new OA\Schema(type: 'string', example: 'success'))
        ]
    )]
    #[OA\Response(
        response: 400,
        description: 'Paramètre manquant',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Missing parameter: login')
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Identifiants incorrects',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Invalid credentials')
            ]
        ),
        headers: [
            new OA\Header(header: 'X-Login-Logged', description: 'Tentative loggée', schema: new OA\Schema(type: 'string', example: 'true')),
            new OA\Header(header: 'X-Login-Status', description: 'Statut échec', schema: new OA\Schema(type: 'string', example: 'failure'))
        ]
    )]
    #[OA\Response(
        response: 429,
        description: 'Trop de tentatives - IP ou login bloqué',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Too many failed attempts. IP temporarily blocked.'),
                new OA\Property(property: 'retry_after', type: 'integer', description: 'Temps d\'attente en secondes', example: 3600)
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Erreur serveur',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'An error occurred during authentication'),
                new OA\Property(property: 'message', type: 'string', example: 'Database connection failed')
            ]
        )
    )]
    public function index(Request $request): Response
    {
        // Traitement des requêtes OPTIONS pour CORS
        if ($request->getMethod() === 'OPTIONS') {
            return new Response('', Response::HTTP_OK, [
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'POST, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, X-CSRF-Token',
            ]);
        }

        $data = new ParameterBag($this->serializer->normalize(json_decode($request->getContent()), 'json'));
        
        // Validation des paramètres requis
        foreach (['login', 'password'] as $key) {
            if (!$data->has($key)) {
                $errorDto = ErrorResponseDTO::create("Missing parameter: $key");
                return $this->json($errorDto->toArray(), Response::HTTP_BAD_REQUEST);
            }
        }

        $login = $data->get('login');
        $password = $data->get('password');

        try {
            // Rechercher l'utilisateur
            $user = $this->userRepository->findOneBy(['username' => $login]);
            
            if (!$user) {
                // Utilisateur non trouvé - le logging sera automatique via l'attribut
                $errorDto = ErrorResponseDTO::create('Invalid credentials');
                return $this->json($errorDto->toArray(), Response::HTTP_UNAUTHORIZED);
            }

            // Vérifier le mot de passe
            if (!$this->passwordHasher->isPasswordValid($user, $password)) {
                // Mot de passe incorrect - le logging sera automatique via l'attribut
                $errorDto = ErrorResponseDTO::create('Invalid credentials');
                return $this->json($errorDto->toArray(), Response::HTTP_UNAUTHORIZED);
            }

            // Connexion réussie - le logging sera automatique via l'attribut
            // Mettre à jour la dernière visite
            $user->setLastVisit(new \DateTime());
            
            // Persister les changements
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            // Créer le DTO utilisateur
            $userDto = new LoginUserDTO(
                id: $user->getId(),
                username: $user->getUsername(),
                roles: $user->getRoles(),
                lastVisit: $user->getLastVisit()?->format('Y-m-d H:i:s')
            );

            // Créer le DTO de réponse de succès
            $successDto = new LoginSuccessResponseDTO(
                success: true,
                message: 'Login successful',
                user: $userDto
            );

            return $this->json($successDto->toArray(), Response::HTTP_OK);

        } catch (\Exception $e) {
            // Log l'erreur - le logging sera automatique via l'attribut
            $errorDto = ErrorResponseDTO::withMessage(
                'An error occurred during authentication',
                $e->getMessage()
            );
            return $this->json($errorDto->toArray(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
