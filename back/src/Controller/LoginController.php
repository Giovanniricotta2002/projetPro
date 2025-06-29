<?php

namespace App\Controller;

use App\Attribute\LogLogin;
use App\DTO\ErrorResponseDTO;
use App\DTO\JWTLoginResponseDTO;
use App\DTO\JWTTokensDTO;
use App\DTO\LoginUserDTO;
use App\Repository\UtilisateurRepository;
use App\Service\InitSerializerService;
use App\Service\JWTService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Serializer\Serializer;

#[Route('/api/login', name: 'app_login')]
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
    public function loginOptions(Request $request): Response
    {
        return new Response('', Response::HTTP_OK, [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'POST, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, X-CSRF-Token',
        ]);
    }
}
