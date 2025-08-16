<?php

namespace App\Controller;

use App\DTO\{ErrorResponseDTO, ForumCreateRequestDTO, ForumResponseDTO};
use App\Entity\Forum;
use App\Repository\{CategorieForumRepository, ForumRepository, UtilisateurRepository};
use App\Service\{AuthenticatedUserService, HttpOnlyCookieService, InitSerializerService, JWTService};
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{ParameterBag, Request, Response};
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Serializer;

#[Route('/api/forum', name: 'app_forum')]
#[OA\Tag(name: 'Forum', description: 'Gestion des forums')]
final class ForumController extends AbstractController
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
        return $this->render('forum/index.html.twig', [
            'controller_name' => 'ForumController',
        ]);
    }

    #[Route('/', name: '_forum', methods: ['GET'])]
    #[OA\Get(
        path: '/api/forum/',
        summary: 'Liste des forums',
        description: 'Retourne la liste des forums',
        tags: ['Forum'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des forums',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        ref: new Model(type: ForumResponseDTO::class)
                    )
                )
            ),
        ]
    )]
    public function getForums(ForumRepository $fRepository): Response
    {
        $forums = $fRepository->findAll();
        $dtos = array_map(fn ($forum) => ForumResponseDTO::fromEntity($forum), $forums);

        return $this->json($this->serializer->normalize($dtos, 'json'));
    }

    #[Route('/', name: '_create_forum', methods: ['POST'])]
    #[OA\Post(
        path: '/api/forum/',
        summary: 'Créer un forum',
        description: 'Crée un nouveau forum',
        tags: ['Forum'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: ForumCreateRequestDTO::class))
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Forum créé',
                content: new OA\JsonContent(
                    ref: new Model(type: ForumResponseDTO::class)
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Erreur de validation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean'),
                        new OA\Property(property: 'message', type: 'string'),
                    ]
                )
            ),
        ]
    )]
    public function createForum(Request $request, CategorieForumRepository $cfRepository, UtilisateurRepository $uRepository): Response
    {
        $datas = new ParameterBag($this->serializer->decode($request->getContent(), 'json'));
        foreach (['titre', 'categories', 'description', 'ordreAffichage'] as $value) {
            if (!$datas->has($value)) {
                return $this->json([
                    'success' => false,
                    'message' => sprintf('Missing field: %s', $value),
                ], 400);
            }
        }

        $categories = $cfRepository->find($datas->get('categories'));

        $forum = new Forum();
        $forum->setTitre($datas->get('titre'));
        $forum->setDateCreation(new \DateTime());
        $forum->setDescription($datas->get('description', null));
        $forum->setOrdreAffichage($datas->get('ordreAffichage', 0));
        $forum->setVisible($datas->get('visible', true));
        $forum->addCategorieForum($categories);
        $forum->setSlug($datas->get('slug', ''));
        // Récupérer l'utilisateur connecté via le service dédié
        $authenticatedUserService = new AuthenticatedUserService(
            $uRepository,
            $this->jwtService,
            $this->cookieService
        );
        [$user, $error] = $authenticatedUserService->getAuthenticatedUser($request);
        if ($error) {
            return $this->json($error->toArray(), 401);
        }
        $forum->setUtilisateur($user);

        try {
            $this->entityManager->persist($forum);
            $this->entityManager->flush();
        } catch (ORMException $orm) {
            return $this->json(
                ErrorResponseDTO::withMessage('Erreur lors de la création du forum', $orm->getMessage())->toArray(),
                400
            );
        }

        $dto = ForumResponseDTO::fromEntity($forum);

        return $this->json($this->serializer->normalize($dto, 'json'));
    }

    #[Route('/{forum<\d*>}', name: '_delete_forum', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/forum/{forum}',
        summary: 'Supprimer un forum',
        description: 'Supprime un forum par son ID',
        tags: ['Forum'],
        parameters: [
            new OA\Parameter(
                name: 'forum',
                in: 'path',
                required: true,
                description: 'ID du forum',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Forum supprimé',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                    ]
                )
            ),
        ]
    )]
    public function deleteForum(Forum $forum): Response
    {
        try {
            $forum
                ->setVisible(false)
                ->setDateCloture(new \DateTime())
            ;

            $this->entityManager->flush();

            return $this->json(['success' => true]);
        } catch (ORMException $orm) {
            return $this->json(
                ErrorResponseDTO::withMessage('Erreur lors de la suppression du forum', $orm->getMessage())->toArray(),
                400
            );
        }
    }
}
