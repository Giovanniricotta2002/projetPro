<?php

namespace App\Controller;

use App\Attribute\LogLogin;
use App\DTO\{ErrorResponseDTO, JWTLoginResponseDTO, JWTTokensDTO, LoginUserDTO};
use App\Entity\Utilisateur;
use App\Enum\UserStatus;
use App\Repository\UtilisateurRepository;
use App\Service\{HttpOnlyCookieService, InitSerializerService, JWTService};
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Serializer\Serializer;

#[Route('/api', name: 'app_login')]
final class LoginController extends AbstractController
{
    private Serializer $serializer;

    public function __construct(
        private readonly UtilisateurRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface $entityManager,
        private readonly JWTService $jwtService,
        private readonly HttpOnlyCookieService $cookieService,
    ) {
        $init = new InitSerializerService();
        $this->serializer = $init->serializer;
    }

    #[Route('/login', name: '_log', methods: ['POST'])]
    #[IsCsrfTokenValid('authenticate', tokenKey: 'X-CSRF-Token', methods: ['POST'])]
    #[LogLogin(enabled: true, usernameField: 'login', passwordField: 'password', checkBlocking: true, maxIpAttempts: 5, maxLoginAttempts: 3, ipBlockDuration: 60, loginBlockDuration: 30)]
    #[OA\Post(
        path: '/api/login',
        operationId: 'authenticateUser',
        summary: 'Authentifier un utilisateur',
        description: 'Authentifie un utilisateur avec son login et mot de passe. Inclut une protection contre les attaques par force brute.',
        security: [['csrfToken' => []]],
        tags: ['Authentication']
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Identifiants de connexion utilisateur',
        content: new OA\JsonContent(
            type: 'object',
            required: ['login', 'password'],
            properties: [
                new OA\Property(property: 'login', type: 'string', description: 'Nom d\'utilisateur ou adresse email', example: 'john.doe'),
                new OA\Property(property: 'password', type: 'string', description: 'Mot de passe de l\'utilisateur', format: 'password', example: 'motDePasseSecret123'),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Connexion réussie avec tokens JWT',
        content: new OA\JsonContent(ref: new Model(type: JWTLoginResponseDTO::class))
    )]
    #[OA\Response(
        response: 401,
        description: 'Identifiants incorrects',
        content: new OA\JsonContent(ref: new Model(type: ErrorResponseDTO::class))
    )]
    #[OA\Response(
        response: 429,
        description: 'Trop de tentatives - Protection anti-brute force',
        content: new OA\JsonContent(ref: new Model(type: ErrorResponseDTO::class))
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

            // Définir les cookies httpOnly pour les tokens JWT
            $this->cookieService->setJwtCookies($response, $request, $tokens);

            // Ajouter les headers de debugging JWT
            $tokenInfo = $this->jwtService->getTokenInfo($tokens['access_token']);
            if ($tokenInfo->valid) {
                $response->headers->set('X-JWT-Token-ID', $tokenInfo->tokenId ?? 'unknown');
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

    #[Route('/login', name: '_log_options', methods: ['OPTIONS'])]
    public function loginOptions(Request $request): Response
    {
        return new Response('', Response::HTTP_OK, [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'POST, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, X-CSRF-Token',
        ]);
    }

    #[Route('/register', name: '_register', methods: ['POST'])]
    #[IsCsrfTokenValid('authenticate', tokenKey: 'X-CSRF-Token', methods: ['POST'])]
    public function register(Request $request): Response
    {
        $data = new ParameterBag($this->serializer->normalize(json_decode($request->getContent()), 'json'));

        // Validation des paramètres requis
        foreach (['username', 'password'] as $key) {
            if (!$data->has($key)) {
                $errorDto = ErrorResponseDTO::create("Missing parameter: {$key}");

                return $this->json($errorDto->toArray(), Response::HTTP_BAD_REQUEST);
            }
        }

        $username = $data->get('username');
        $password = $data->get('password');

        // Sécurité supplémentaire sur le login
        if (strlen($username) < 3) {
            $errorDto = ErrorResponseDTO::create('Login must be at least 3 characters long');

            return $this->json($errorDto->toArray(), Response::HTTP_BAD_REQUEST);
        }
        if (strlen($username) > 180) {
            $errorDto = ErrorResponseDTO::create('Login must be less than 180 characters');

            return $this->json($errorDto->toArray(), Response::HTTP_BAD_REQUEST);
        }

        // Sécurité supplémentaire sur le mot de passe
        if (strlen($password) < 6) {
            $errorDto = ErrorResponseDTO::create('Password must be at least 6 characters long');

            return $this->json($errorDto->toArray(), Response::HTTP_BAD_REQUEST);
        }

        try {
            // Vérifier si l'utilisateur existe déjà
            if ($this->userRepository->findOneBy(['username' => $username])) {
                $errorDto = ErrorResponseDTO::create('Username already exists');

                return $this->json($errorDto->toArray(), Response::HTTP_CONFLICT);
            }

            // Créer un nouvel utilisateur
            $user = new Utilisateur();
            $user->setUsername($username);
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
            $user->setRoles(['ROLE_USER']);
            $user->setStatus(UserStatus::ACTIVE);
            $user->setLastVisit(new \DateTime());

            // Persister l'utilisateur
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            // Connexion réussie - le logging sera automatique via l'attribut
            return $this->json(['message' => 'User registered successfully'], Response::HTTP_CREATED);
        } catch (ORMException $orm) {
            // Log l'erreur - le logging sera automatique via l'attribut
            $errorDto = ErrorResponseDTO::withMessage(
                'An error occurred during registration',
                $orm->getMessage()
            );

            return $this->json($errorDto->toArray(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/logout', name: '_logout', methods: ['POST'])]
    #[OA\Post(
        path: '/api/tokens/logout',
        operationId: 'logoutUser',
        summary: 'Déconnecter un utilisateur',
        description: 'Supprime les cookies de session et invalide les tokens',
        tags: ['JWT Tokens']
    )]
    #[OA\Response(
        response: 200,
        description: 'Déconnexion réussie',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Logout successful'),
            ]
        )
    )]
    public function logout(Request $request): Response
    {
        $response = $this->json(['message' => 'Logout successful']);

        // Supprimer les cookies httpOnly
        $this->cookieService->clearJwtCookies($response, $request);

        return $response;
    }
}
