<?php

namespace App\Controller;

use Doctrine\ORM\Query\Parameter;
use App\DTO\ErrorResponseDTO;
use App\DTO\CsrfTokenResponseDTO;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints\Yaml;

#[Route('/api/csrfToken')]
#[OA\Tag(name: 'CSRF Token', description: 'Gestion des tokens CSRF pour la sécurité des formulaires')]
final class ApiCSRFTokenController extends AbstractController
{
    private Serializer $serializer;
    public function __construct()
    {
        $normalizer = [new ObjectNormalizer()];
        $encoders = [new JsonEncode(), new CsvEncoder(), new YamlEncoder(), new XmlEncoder()];

        $this->serializer = new Serializer($normalizer, $encoders);
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
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'csrfToken',
                    type: 'string',
                    description: 'Le token CSRF généré',
                    example: 'abc123def456ghi789'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Erreur serveur lors de la génération du token'
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
        content: new OA\JsonContent(
            type: 'object',
            required: ['csrfToken'],
            properties: [
                new OA\Property(
                    property: 'csrfToken',
                    type: 'string',
                    description: 'Le token CSRF à vérifier',
                    example: 'abc123def456ghi789'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 204,
        description: 'Token CSRF valide'
    )]
    #[OA\Response(
        response: 400,
        description: 'Token CSRF manquant',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'error',
                    type: 'string',
                    example: 'CSRF token is missing'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 403,
        description: 'Token CSRF invalide',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'error',
                    type: 'string',
                    example: 'Invalid CSRF token'
                )
            ]
        )
    )]
    public function verifyToken(Request $request, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $data = new ParameterBag($this->serializer->decode($request->getContent(), 'json'));

        if (!$data->has('csrfToken')) {
            $errorDto = ErrorResponseDTO::create('CSRF token is missing');
            return $this->json($errorDto->toArray(), Response::HTTP_BAD_REQUEST);
        }

        if (!$csrfTokenManager->isTokenValid(new CsrfToken('authenticate', $data->get('csrfToken')))) {
            $errorDto = ErrorResponseDTO::create('Invalid CSRF token');
            return $this->json($errorDto->toArray(), Response::HTTP_FORBIDDEN);
        }

        return $this->json([], Response::HTTP_NO_CONTENT);
    }
}
