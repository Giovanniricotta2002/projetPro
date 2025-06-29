<?php

namespace App\Controller;

use App\DTO\ErrorResponseDTO;
use App\DTO\JWTTokensDTO;
use App\DTO\TokenInfoResponseDTO;
use App\DTO\TokenRefreshRequestDTO;
use App\DTO\TokenRefreshResponseDTO;
use App\DTO\TokenValidationRequestDTO;
use App\DTO\TokenValidationResponseDTO;
use App\Repository\UtilisateurRepository;
use App\Service\InitSerializerService;
use App\Service\JWTService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Serializer;

#[Route('/api/tokens', name: 'app_tokens')]
final class TokenController extends AbstractController
{
    private Serializer $serializer;

    public function __construct(
        private readonly JWTService $jwtService,
        private readonly UtilisateurRepository $userRepository,
    ) {
        $init = new InitSerializerService();
        $this->serializer = $init->serializer;
    }

    #[Route('/refresh', name: '_refresh', methods: ['POST'])]
    #[OA\Post(
        path: '/api/tokens/refresh',
        operationId: 'refreshToken',
        summary: 'Rafraîchir un token JWT',
        description: 'Génère un nouveau token d\'accès à partir d\'un refresh token valide',
        tags: ['JWT Tokens']
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Refresh token à utiliser',
        content: new OA\JsonContent(ref: new Model(type: TokenRefreshRequestDTO::class))
    )]
    #[OA\Response(
        response: 200,
        description: 'Token rafraîchi avec succès',
        content: new OA\JsonContent(ref: new Model(type: TokenRefreshResponseDTO::class))
    )]
    #[OA\Response(
        response: 400,
        description: 'Refresh token invalide ou expiré',
        content: new OA\JsonContent(ref: new Model(type: ErrorResponseDTO::class))
    )]
    public function refresh(Request $request): Response
    {
        $data = new ParameterBag($this->serializer->normalize(json_decode($request->getContent()), 'json'));

        try {
            // Valider et créer le DTO de requête depuis le ParameterBag
            $requestDto = TokenRefreshRequestDTO::fromParameterBag($data);

            if (!$requestDto->hasValidFormat()) {
                $errorDto = ErrorResponseDTO::create('Invalid refresh token format');

                return $this->json($errorDto->toArray(), Response::HTTP_BAD_REQUEST);
            }
        } catch (\InvalidArgumentException $e) {
            $errorDto = ErrorResponseDTO::create($e->getMessage());

            return $this->json($errorDto->toArray(), Response::HTTP_BAD_REQUEST);
        }

        try {
            // Valider le refresh token
            $tokenPayload = $this->jwtService->validateToken($requestDto->refreshToken);

            // Vérifier que c'est bien un refresh token
            if (!$this->jwtService->isTokenType($tokenPayload, 'refresh')) {
                $errorDto = ErrorResponseDTO::create('Invalid token type. Expected refresh token.');

                return $this->json($errorDto->toArray(), Response::HTTP_BAD_REQUEST);
            }

            // Récupérer l'utilisateur
            $userId = (int) $tokenPayload['sub'];
            $user = $this->userRepository->find($userId);

            if (!$user) {
                $errorDto = ErrorResponseDTO::create('User not found');

                return $this->json($errorDto->toArray(), Response::HTTP_UNAUTHORIZED);
            }

            // Générer de nouveaux tokens
            $newTokens = $this->jwtService->generateTokenPair($user, [
                'refreshed_from' => $tokenPayload['jti'] ?? 'unknown',
                'refresh_time' => time(),
                'ip_address' => $request->getClientIp(),
                'user_agent' => $request->headers->get('User-Agent'),
            ]);

            // Créer les DTOs de réponse
            $tokensDto = JWTTokensDTO::forRefreshResponse($newTokens);
            $responseDto = TokenRefreshResponseDTO::success($tokensDto);

            return $this->json($responseDto->toArray());
        } catch (\InvalidArgumentException $e) {
            $errorDto = ErrorResponseDTO::withMessage(
                'Invalid refresh token',
                $e->getMessage()
            );

            $statusCode = $e->getCode() === 401 ? Response::HTTP_UNAUTHORIZED : Response::HTTP_BAD_REQUEST;

            return $this->json($errorDto->toArray(), $statusCode);
        } catch (\Exception $e) {
            $errorDto = ErrorResponseDTO::withMessage(
                'An error occurred while refreshing token',
                $e->getMessage()
            );

            return $this->json($errorDto->toArray(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/validate', name: '_validate', methods: ['POST'])]
    public function validate(Request $request): Response
    {
        $data = new ParameterBag($this->serializer->normalize(json_decode($request->getContent()), 'json'));

        try {
            // Valider et créer le DTO de requête depuis le ParameterBag
            $requestDto = TokenValidationRequestDTO::fromParameterBag($data);

            if (!$requestDto->hasValidFormat()) {
                $responseDto = TokenValidationResponseDTO::invalid('Invalid token format');

                return $this->json($responseDto->toArray(), Response::HTTP_BAD_REQUEST);
            }
        } catch (\InvalidArgumentException $e) {
            $responseDto = TokenValidationResponseDTO::invalid($e->getMessage());

            return $this->json($responseDto->toArray(), Response::HTTP_BAD_REQUEST);
        }

        $tokenInfo = $this->jwtService->getTokenInfo($requestDto->token);
        // Ensure $tokenInfo is an array, not a DTO object
        if ($tokenInfo instanceof TokenInfoResponseDTO) {
            $tokenInfoArray = $tokenInfo->toArray();
        } else {
            $tokenInfoArray = $tokenInfo;
        }
        $responseDto = TokenValidationResponseDTO::fromTokenInfo($tokenInfoArray);

        $statusCode = $responseDto->valid ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST;

        return $this->json($responseDto->toArray(), $statusCode);
    }

    #[Route('/info', name: '_info', methods: ['GET'])]
    public function info(Request $request): Response
    {
        $authHeader = $request->headers->get('Authorization');
        $token = $this->jwtService->extractTokenFromHeader($authHeader);

        if (!$token) {
            $errorDto = ErrorResponseDTO::create('Missing or invalid Authorization header');

            return $this->json($errorDto->toArray(), Response::HTTP_UNAUTHORIZED);
        }

        try {
            // Valider et obtenir le payload complet du token
            $tokenPayload = $this->jwtService->validateToken($token);
            $responseDto = TokenInfoResponseDTO::validWithDetails($tokenPayload);

            return $this->json($responseDto->toArray(), Response::HTTP_OK);
        } catch (\InvalidArgumentException $e) {
            $responseDto = TokenInfoResponseDTO::invalid($e->getMessage());

            return $this->json($responseDto->toArray(), Response::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            $errorDto = ErrorResponseDTO::withMessage(
                'An error occurred while processing token',
                $e->getMessage()
            );

            return $this->json($errorDto->toArray(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
