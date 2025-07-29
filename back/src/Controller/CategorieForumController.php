<?php

namespace App\Controller;

use App\DTO\CategorieForumResponseDTO;
use App\Repository\{CategorieForumRepository, UtilisateurRepository};
use App\Service\{HttpOnlyCookieService, InitSerializerService, JWTService};
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Serializer;

#[Route('/api/categorie-forum', name: 'app_categorie_forum')]
#[OA\Tag(name: 'CategorieForum', description: 'Gestion des catégories de forum')]
final class CategorieForumController extends AbstractController
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

    #[Route('/', name: '_load', methods: ['GET'])]
    #[OA\Get(
        path: '/api/categorie-forum/',
        summary: 'Liste des catégories de forum',
        description: 'Retourne la liste des catégories de forum',
        tags: ['CategorieForum'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des catégories',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        ref: new Model(type: CategorieForumResponseDTO::class)
                    )
                )
            ),
        ]
    )]
    public function loadCategories(CategorieForumRepository $cfRepository): Response
    {
        $categories = $cfRepository->findAll();
        $dtos = array_map(fn ($cat) => CategorieForumResponseDTO::fromEntity($cat), $categories);

        return $this->json($this->serializer->normalize($dtos, 'json'));
    }
}
