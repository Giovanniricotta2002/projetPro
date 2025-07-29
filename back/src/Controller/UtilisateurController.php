<?php

namespace App\Controller;

use App\DTO\UtilisateurResponseDTO;
use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use App\Service\{HttpOnlyCookieService, InitSerializerService, JWTService};
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Serializer;

#[Route('/api/utilisateur', name: 'app_api_utilisateur')]
final class UtilisateurController extends AbstractController
{
    private Serializer $serializer;

    public function __construct(
        private readonly UtilisateurRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly JWTService $jwtService,
        private readonly HttpOnlyCookieService $cookieService,
    ) {
        $init = new InitSerializerService();
        $this->serializer = $init->serializerAndDate();
    }

    public function index(): Response
    {
        return $this->render('utilisateur/index.html.twig', [
            'controller_name' => 'UtilisateurController',
        ]);
    }

    #[OA\Get(
        path: '/api/utilisateur/{utilisateur}',
        summary: 'Récupérer un utilisateur',
        description: 'Retourne les informations d\'un utilisateur par son ID',
        tags: ['Utilisateur'],
        parameters: [
            new OA\Parameter(
                name: 'utilisateur',
                in: 'path',
                required: true,
                description: 'ID de l\'utilisateur',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Données de l\'utilisateur',
                content: new OA\JsonContent(
                    ref: new Model(type: UtilisateurResponseDTO::class)
                )
            ),
        ]
    )]
    public function getUtilisateur(Utilisateur $utilisateur): Response
    {
        $dto = UtilisateurResponseDTO::fromEntity($utilisateur);

        return $this->json($this->serializer->normalize($dto, 'json'));
    }
}
