<?php

namespace App\Controller;

use App\DTO\{ErrorResponseDTO, PostResponseDTO};
use App\Entity\{Forum, Post};
use App\Repository\{ForumRepository, UtilisateurRepository};
use App\Service\{AuthenticatedUserService, HttpOnlyCookieService, InitSerializerService, JWTService};
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{ParameterBag, Request, Response};
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Serializer;

#[Route('/api/post', name: 'app_post')]
final class PostController extends AbstractController
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

    #[Route('/{forum<\d*>}/posts', name: '_forum_posts', methods: ['GET'])]
    #[OA\Get(
        path: '/api/post/{forum}/posts',
        summary: 'Liste des posts d\'un forum',
        description: 'Retourne la liste des posts pour un forum donné',
        tags: ['Post'],
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
                description: 'Liste des posts',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        ref: new Model(type: PostResponseDTO::class)
                    )
                )
            ),
        ]
    )]
    public function showForumPosts(Forum $forum): Response
    {
        $posts = $forum->getPost();
        $dtos = array_map(fn ($post) => PostResponseDTO::fromEntity($post), $posts->toArray());

        return $this->json($this->serializer->normalize($dtos, 'json'));
    }

    #[Route('/{post<\d*>}', name: '_remove_post', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/post/{post}',
        summary: 'Supprimer un post',
        description: 'Supprime un post par son ID',
        tags: ['Post'],
        parameters: [
            new OA\Parameter(
                name: 'post',
                in: 'path',
                required: true,
                description: 'ID du post',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Post supprimé avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Post supprimé avec succès'),
                    ]
                )
            ),
        ]
    )]
    public function removePost(Post $post): Response
    {
        $this->entityManager->remove($post);
        $this->entityManager->flush();

        return $this->json(['message' => 'Post supprimé avec succès']);
    }

    #[Route('/', name: '_create_post', methods: ['POST'])]
    #[OA\Post(
        path: '/api/post/',
        summary: 'Créer un post',
        description: 'Crée un nouveau post dans un forum',
        tags: ['Post'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'titre', type: 'string', example: 'Titre du post'),
                    new OA\Property(property: 'utilisateur', type: 'integer', example: 1),
                    new OA\Property(property: 'forum', type: 'integer', example: 1),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Post créé avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Post créé avec succès'),
                    ]
                )
            ),
        ]
    )]
    public function createPost(Request $request, ForumRepository $fRepository, AuthenticatedUserService $authenticatedUserService): Response
    {
        $datas = new ParameterBag($this->serializer->decode($request->getContent(), 'json'));
        foreach (['titre', 'forum'] as $field) {
            if (!$datas->has($field)) {
                return $this->json(['error' => "Le champ '{$field}' est requis."], 400);
            }
        }

        $forum = $fRepository->find($datas->get('forum'));

        $post = new Post();
        $post
            ->setTitre($datas->get('titre'))
            ->setDateCreation(new \DateTime())
            ->setVues(0)
            ->setVerrouille(false)
            ->setEpingle(false)
            ->setForum($forum)
        ;

        [$user, $error] = $authenticatedUserService->getAuthenticatedUser($request);
        if ($error) {
            return $this->json($error->toArray(), 401);
        }

        $post->setUtilisateur($user);

        try {
            $this->entityManager->persist($post);
            $this->entityManager->flush();
        } catch (ORMException $orm) {
            return $this->json(
                ErrorResponseDTO::withMessage('Erreur lors de la création du post', $orm->getMessage())->toArray(),
                400
            );
        }

        // Retourne le DTO du post créé
        $dto = PostResponseDTO::fromEntity($post);

        return $this->json($this->serializer->normalize($dto, 'json'));
    }

    #[Route('/{post<\d*>}', name: '_update_post', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/post/{post}',
        summary: 'Mettre à jour un post',
        description: 'Met à jour un post existant',
        tags: ['Post'],
        parameters: [
            new OA\Parameter(
                name: 'post',
                in: 'path',
                required: true,
                description: 'ID du post',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'epingle', type: 'boolean', example: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Post mis à jour avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Post mis à jour avec succès'),
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Erreur lors de la mise à jour',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Message d\'erreur'),
                    ]
                )
            ),
        ]
    )]
    public function updatePost(Post $post, Request $request): Response
    {
        $data = new ParameterBag($this->serializer->decode($request->getContent(), 'json'));
        foreach (['epingle'] as $field) {
            if (!$data->has($field)) {
                return $this->json(['error' => "Le champ '{$field}' est requis."], 400);
            }
        }

        try {
            $post->setEpingle($data->get('epingle'));
            $this->entityManager->flush();
            $dto = PostResponseDTO::fromEntity($post);

            return $this->json($this->serializer->normalize($dto, 'json'));
        } catch (ORMException $orm) {
            return $this->json(['error' => $orm->getMessage()], 500);
        }
    }
}
