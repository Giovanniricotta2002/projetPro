<?php

namespace App\Controller;

use App\DTO\CsrfTokenResponseDTO;
use App\DTO\CsrfTokenVerificationRequestDTO;
use App\DTO\CsrfTokenVerificationResponseDTO;
use App\DTO\ErrorResponseDTO;
use App\Service\InitSerializerService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/csrfToken')]
final class ApiCSRFTokenController extends AbstractController
{
    private Serializer $serializer;

    public function __construct(
        private readonly ValidatorInterface $validator,
    ) {
        $init = new InitSerializerService();
        $this->serializer = $init->serializer;
    }

    #[Route('', name: '_generate', methods: ['GET', 'OPTIONS'])]
    #[OA\Get(
        path: '/api/csrfToken',
        operationId: 'generateCsrfToken',
        summary: 'Générer un token CSRF',
        description: 'Génère un nouveau token CSRF pour sécuriser les formulaires de l\'application',
        tags: ['CSRF Token']
    )]
    #[OA\Response(
        response: 200,
        description: 'Token CSRF généré avec succès',
        content: new OA\JsonContent(ref: new Model(type: CsrfTokenResponseDTO::class))
    )]
    #[OA\Response(
        response: 500,
        description: 'Erreur serveur lors de la génération du token',
        content: new OA\JsonContent(ref: new Model(type: ErrorResponseDTO::class))
    )]
    public function generateToken(CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $token = $csrfTokenManager->getToken('authenticate')->getValue();

        $responseDto = CsrfTokenResponseDTO::create($token);

        return $this->json($responseDto->toArray(), Response::HTTP_OK);
    }

    #[Route('/verify', name: '_verify', methods: ['POST'])]
    #[OA\Post(
        path: '/api/csrfToken/verify',
        operationId: 'verifyCsrfToken',
        summary: 'Vérifier un token CSRF',
        description: 'Vérifie la validité d\'un token CSRF fourni par le client',
        tags: ['CSRF Token']
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Token CSRF à vérifier',
        content: new OA\JsonContent(ref: new Model(type: CsrfTokenVerificationRequestDTO::class))
    )]
    #[OA\Response(
        response: 200,
        description: 'Token CSRF valide',
        content: new OA\JsonContent(ref: new Model(type: CsrfTokenVerificationResponseDTO::class))
    )]
    #[OA\Response(
        response: 400,
        description: 'Token CSRF manquant ou format invalide',
        content: new OA\JsonContent(ref: new Model(type: ErrorResponseDTO::class))
    )]
    #[OA\Response(
        response: 403,
        description: 'Token CSRF invalide',
        content: new OA\JsonContent(ref: new Model(type: ErrorResponseDTO::class))
    )]
    #[OA\Response(
        response: 422,
        description: 'Erreurs de validation des données',
        content: new OA\JsonContent(ref: new Model(type: ErrorResponseDTO::class))
    )]
    public function verifyToken(Request $request, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $data = new ParameterBag($this->serializer->normalize(json_decode($request->getContent()), 'json'));

        if ($data->all() === null) {
            $errorDto = ErrorResponseDTO::create('Invalid JSON format');

            return $this->json($errorDto->toArray(), Response::HTTP_BAD_REQUEST);
        }

        try {
            // Créer le DTO depuis le ParameterBag directement
            $requestDto = CsrfTokenVerificationRequestDTO::fromParameterBag($data);

            // Valider le DTO
            $violations = $this->validator->validate($requestDto);
            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[] = $violation->getMessage();
                }
                $errorDto = ErrorResponseDTO::withMessage(
                    'Validation failed',
                    implode('; ', $errors)
                );

                return $this->json($errorDto->toArray(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        } catch (\InvalidArgumentException $e) {
            $errorDto = ErrorResponseDTO::create($e->getMessage());

            return $this->json($errorDto->toArray(), Response::HTTP_BAD_REQUEST);
        }

        try {
            // Vérifier la validité du token CSRF
            $csrfToken = new CsrfToken('authenticate', $requestDto->csrfToken);

            if (!$csrfTokenManager->isTokenValid($csrfToken)) {
                $errorDto = ErrorResponseDTO::create('Invalid CSRF token');

                return $this->json($errorDto->toArray(), Response::HTTP_FORBIDDEN);
            }

            // Token valide - retourner une réponse de succès
            $responseDto = CsrfTokenVerificationResponseDTO::createValid();

            return $this->json($responseDto->toArray(), Response::HTTP_OK);
        } catch (\Exception $e) {
            $errorDto = ErrorResponseDTO::withMessage(
                'Error occurred while verifying CSRF token',
                $e->getMessage()
            );

            return $this->json($errorDto->toArray(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
