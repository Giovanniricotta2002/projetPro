<?php

namespace App\Controller;

use App\Attribute\LogLogin;
use App\DTO\ErrorResponseDTO;
use App\DTO\JWTLoginResponseDTO;
use App\DTO\JWTTokensDTO;
use App\DTO\LoginUserDTO;
use App\Repository\UtilisateurRepository;
use App\Service\JWTService;
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

#[Route('/api/login', name: 'app_login')]
#[OA\Tag(
    name: 'Authentication',
    description: 'Endpoints de gestion de l\'authentification utilisateur avec système de logging automatique et protection anti-brute force'
)]
#[OA\Info(
    version: '1.0.0',
    title: 'MuscuScope Authentication API',
    description: 'API d\'authentification sécurisée avec logging automatique des tentatives de connexion'
)]
#[OA\Server(
    url: '/api',
    description: 'Serveur API principal'
)]
#[OA\SecurityScheme(
    securityScheme: 'csrf_token',
    type: 'apiKey',
    name: 'X-CSRF-Token',
    in: 'header',
    description: 'Token CSRF requis pour toutes les opérations sensibles'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Token JWT d\'authentification dans le header Authorization'
)]
final class LoginController extends AbstractController
{
    private Serializer $serializer;

    public function __construct(
        private readonly UtilisateurRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface $entityManager,
        private readonly JWTService $jwtService,
    ) {
        $init = new InitSerializerService();
        $this->serializer = $init->serializer;
    }

    #[Route('', name: '_log', methods: ['POST'])]
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
        description: 'Authentifie un utilisateur avec son login et mot de passe. Inclut une protection contre les attaques par force brute avec blocage automatique par IP et par login. Toutes les tentatives sont automatiquement loggées via l\'attribut #[LogLogin].',
        security: [['csrf_token' => []]],
        tags: ['Authentication'],
        externalDocs: new OA\ExternalDocumentation(
            description: 'Documentation sur la sécurité des API',
            url: 'https://docs.muscuscope.com/security'
        )
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Identifiants de connexion utilisateur',
        content: [
            new OA\JsonContent(
                type: 'object',
                required: ['login', 'password'],
                properties: [
                    new OA\Property(
                        property: 'login',
                        type: 'string',
                        description: 'Nom d\'utilisateur ou adresse email',
                        minLength: 3,
                        maxLength: 255,
                        example: 'john.doe'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        description: 'Mot de passe de l\'utilisateur',
                        format: 'password',
                        minLength: 8,
                        maxLength: 255,
                        example: 'motDePasseSecret123'
                    ),
                ],
                example: [
                    'login' => 'john.doe',
                    'password' => 'motDePasseSecret123',
                ]
            ),
            new OA\XmlContent(
                type: 'object',
                xml: new OA\Xml(name: 'loginRequest'),
                properties: [
                    new OA\Property(property: 'login', type: 'string'),
                    new OA\Property(property: 'password', type: 'string'),
                ]
            ),
        ]
    )]
    #[OA\Parameter(
        name: 'X-CSRF-Token',
        description: 'Token CSRF requis pour la sécurité des requêtes POST. Obtenir via /api/csrf-token',
        in: 'header',
        required: true,
        schema: new OA\Schema(
            type: 'string',
            minLength: 32,
            maxLength: 128,
            pattern: '^[a-zA-Z0-9_-]+$',
            example: 'abc123def456ghi789'
        ),
        examples: [
            new OA\Examples(
                example: 'valid_token',
                summary: 'Token CSRF valide',
                value: 'CSRFToken_1234567890abcdef'
            ),
            new OA\Examples(
                example: 'session_token',
                summary: 'Token de session',
                value: 'sess_abcdef123456789'
            ),
        ]
    )]
    #[OA\Response(
        response: 200,
        description: 'Connexion réussie avec tokens JWT',
        content: new OA\JsonContent(
            ref: new Model(type: JWTLoginResponseDTO::class)
        ),
        headers: [
            new OA\Header(
                header: 'X-Login-Logged',
                description: 'Indique si la tentative a été loggée automatiquement',
                schema: new OA\Schema(type: 'boolean', example: true)
            ),
            new OA\Header(
                header: 'X-Login-Status',
                description: 'Statut de la connexion pour debugging',
                schema: new OA\Schema(type: 'string', enum: ['success', 'failure'], example: 'success')
            ),
            new OA\Header(
                header: 'X-Login-Attempt-Count',
                description: 'Nombre de tentatives de connexion pour cet utilisateur',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Header(
                header: 'X-JWT-Token-ID',
                description: 'Identifiant unique du token JWT généré',
                schema: new OA\Schema(type: 'string', example: 'jwt_64f5b2c1a8e9f')
            ),
        ]
    )]
    #[OA\Response(
        response: 400,
        description: 'Paramètres manquants ou invalides',
        content: new OA\JsonContent(
            ref: new Model(type: ErrorResponseDTO::class)
        ),
        headers: [
            new OA\Header(
                header: 'X-Validation-Errors',
                description: 'Nombre d\'erreurs de validation détectées',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ]
    )]
    #[OA\Response(
        response: 401,
        description: 'Identifiants incorrects ou utilisateur non autorisé',
        content: new OA\JsonContent(
            ref: new Model(type: ErrorResponseDTO::class),
            examples: [
                new OA\Examples(
                    example: 'invalid_credentials',
                    summary: 'Identifiants incorrects',
                    value: [
                        'error' => 'Invalid credentials',
                        'message' => 'The provided username or password is incorrect',
                    ]
                ),
                new OA\Examples(
                    example: 'user_not_found',
                    summary: 'Utilisateur non trouvé',
                    value: [
                        'error' => 'Invalid credentials',
                        'message' => 'User account does not exist',
                    ]
                ),
            ]
        ),
        headers: [
            new OA\Header(
                header: 'X-Login-Logged',
                description: 'Tentative d\'échec loggée automatiquement',
                schema: new OA\Schema(type: 'boolean', example: true)
            ),
            new OA\Header(
                header: 'X-Login-Status',
                description: 'Statut d\'échec pour debugging',
                schema: new OA\Schema(type: 'string', example: 'failure')
            ),
            new OA\Header(
                header: 'X-Failed-Attempts',
                description: 'Nombre de tentatives échouées pour cet utilisateur/IP',
                schema: new OA\Schema(type: 'integer', example: 2)
            ),
        ]
    )]
    #[OA\Response(
        response: 429,
        description: 'Trop de tentatives - Protection contre le brute force activée',
        content: new OA\JsonContent(
            allOf: [
                new OA\Schema(ref: new Model(type: ErrorResponseDTO::class)),
                new OA\Schema(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'retry_after',
                            type: 'integer',
                            description: 'Temps d\'attente en secondes avant la prochaine tentative',
                            example: 3600
                        ),
                        new OA\Property(
                            property: 'block_type',
                            type: 'string',
                            description: 'Type de blocage appliqué',
                            enum: ['ip_blocked', 'login_blocked', 'both_blocked'],
                            example: 'ip_blocked'
                        ),
                        new OA\Property(
                            property: 'attempts_remaining',
                            type: 'integer',
                            description: 'Nombre de tentatives restantes (0 si bloqué)',
                            example: 0
                        ),
                    ]
                ),
            ],
            examples: [
                new OA\Examples(
                    example: 'ip_blocked',
                    summary: 'IP temporairement bloquée',
                    value: [
                        'error' => 'Too many failed attempts. IP temporarily blocked.',
                        'retry_after' => 3600,
                        'block_type' => 'ip_blocked',
                        'attempts_remaining' => 0,
                    ]
                ),
                new OA\Examples(
                    example: 'login_blocked',
                    summary: 'Login temporairement bloqué',
                    value: [
                        'error' => 'Too many failed attempts for this login. Account temporarily blocked.',
                        'retry_after' => 1800,
                        'block_type' => 'login_blocked',
                        'attempts_remaining' => 0,
                    ]
                ),
            ]
        ),
        headers: [
            new OA\Header(
                header: 'Retry-After',
                description: 'Temps d\'attente recommandé en secondes',
                schema: new OA\Schema(type: 'integer', example: 3600)
            ),
            new OA\Header(
                header: 'X-RateLimit-Limit',
                description: 'Limite de tentatives par période',
                schema: new OA\Schema(type: 'integer', example: 5)
            ),
            new OA\Header(
                header: 'X-RateLimit-Remaining',
                description: 'Tentatives restantes dans la période',
                schema: new OA\Schema(type: 'integer', example: 0)
            ),
            new OA\Header(
                header: 'X-RateLimit-Reset',
                description: 'Timestamp de remise à zéro du compteur',
                schema: new OA\Schema(type: 'integer', example: 1640995200)
            ),
        ]
    )]
    #[OA\Response(
        response: 500,
        description: 'Erreur interne du serveur',
        content: new OA\JsonContent(
            ref: new Model(type: ErrorResponseDTO::class),
            examples: [
                new OA\Examples(
                    example: 'database_error',
                    summary: 'Erreur de base de données',
                    value: [
                        'error' => 'An error occurred during authentication',
                        'message' => 'Database connection failed',
                        'code' => 500,
                    ]
                ),
                new OA\Examples(
                    example: 'service_error',
                    summary: 'Erreur de service',
                    value: [
                        'error' => 'An error occurred during authentication',
                        'message' => 'Authentication service temporarily unavailable',
                        'code' => 503,
                    ]
                ),
            ]
        ),
        headers: [
            new OA\Header(
                header: 'X-Error-ID',
                description: 'Identifiant unique de l\'erreur pour le support technique',
                schema: new OA\Schema(type: 'string', example: 'err_123456789')
            ),
            new OA\Header(
                header: 'X-Login-Logged',
                description: 'Erreur loggée automatiquement',
                schema: new OA\Schema(type: 'boolean', example: true)
            ),
        ]
    )]
    public function login(Request $request): Response
    {
        $data = new ParameterBag($this->serializer->normalize(json_decode($request->getContent()), 'json'));

        // Validation des paramètres requis
        foreach (['login', 'password'] as $key) {
            if (!$data->has($key)) {
                $errorDto = ErrorResponseDTO::create("Missing parameter: {$key}");

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

            // Générer les tokens JWT
            $tokens = $this->jwtService->generateTokenPair($user, [
                'ip_address' => $request->getClientIp(),
                'user_agent' => $request->headers->get('User-Agent'),
                'login_method' => 'password',
            ]);

            // Créer le DTO utilisateur
            $userDto = new LoginUserDTO(
                id: $user->getId(),
                username: $user->getUsername(),
                roles: $user->getRoles(),
                lastVisit: $user->getLastVisit()?->format('Y-m-d H:i:s')
            );

            // Créer les DTOs de tokens et de réponse JWT
            $tokensDto = JWTTokensDTO::fromArray($tokens);
            $jwtResponse = JWTLoginResponseDTO::success(
                user: $userDto,
                tokens: $tokensDto,
                message: 'Login successful with JWT tokens'
            );

            $response = $this->json($jwtResponse->toArray(), Response::HTTP_OK);

            // Ajouter les headers de debugging JWT
            $tokenInfo = $this->jwtService->getTokenInfo($tokens['access_token']);
            if ($tokenInfo['valid']) {
                $response->headers->set('X-JWT-Token-ID', $tokenInfo['token_id'] ?? 'unknown');
            }

            return $response;
        } catch (\Exception $e) {
            // Log l'erreur - le logging sera automatique via l'attribut
            $errorDto = ErrorResponseDTO::withMessage(
                'An error occurred during authentication',
                $e->getMessage()
            );

            return $this->json($errorDto->toArray(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('', name: '_log_options', methods: ['OPTIONS'])]
    #[OA\Options(
        path: '/api/login',
        operationId: 'loginPreflight',
        summary: 'Vérification CORS pour l\'endpoint de connexion',
        description: 'Endpoint de pré-vérification CORS pour les requêtes cross-origin vers l\'API de connexion. Requis par les navigateurs pour les requêtes AJAX cross-domain.',
        tags: ['Authentication', 'CORS']
    )]
    #[OA\Response(
        response: 200,
        description: 'Headers CORS autorisés',
        headers: [
            new OA\Header(
                header: 'Access-Control-Allow-Origin',
                description: 'Origines autorisées pour les requêtes cross-origin',
                schema: new OA\Schema(type: 'string', example: '*')
            ),
            new OA\Header(
                header: 'Access-Control-Allow-Methods',
                description: 'Méthodes HTTP autorisées',
                schema: new OA\Schema(type: 'string', example: 'POST, OPTIONS')
            ),
            new OA\Header(
                header: 'Access-Control-Allow-Headers',
                description: 'Headers autorisés dans les requêtes',
                schema: new OA\Schema(type: 'string', example: 'Content-Type, X-CSRF-Token')
            ),
        ]
    )]
    public function loginOptions(Request $request): Response
    {
        return new Response('', Response::HTTP_OK, [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'POST, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, X-CSRF-Token',
        ]);
    }
}
