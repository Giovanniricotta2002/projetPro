<?php

namespace App\Controller;

use App\Entity\Forum;
use App\Entity\Post;
use App\Repository\ForumRepository;
use App\Repository\UtilisateurRepository;
use App\Service\HttpOnlyCookieService;
use App\Service\InitSerializerService;
use App\Service\JWTService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
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

    public function index(): Response
    {
        return $this->render('post/index.html.twig', [
            'controller_name' => 'PostController',
        ]);
    }

    #[Route('/{forum<\d*>}/posts', name: '_forum_posts', methods: ['GET'])]
    public function showForumPosts(Forum $forum): Response
    {
        $context = [
            AbstractNormalizer::ATTRIBUTES => [
                'id',
                'titre',
                'dateCreation',
                'description',
                'ordreAffichage',
                'visible',
                'slug',
                'createdAt',
                'post' => [
                    'id',
                    'titre',
                    'dateCreation',
                    'vues',
                    'verrouille',
                    'epingle',
                ],
                'categorieForums' => [
                    'id',
                    'name',
                    'ordre',
                    'slug',
                    'createdAt',
                ],
                'utilisateur' => ['id', 'username', 'anonimus'],
            ],
        ];

        return $this->json($this->serializer->normalize($forum, 'json', $context));
    }

    #[Route('/{post<\d*>}', name: '_remove_post', methods: ['DELETE'])]
    public function removePost(Post $post): Response
    {
        $this->entityManager->remove($post);
        $this->entityManager->flush();

        return $this->json(['message' => 'Post supprimé avec succès']);
    }

    #[Route('/', name: '_create_post', methods: ['POST'])]
    public function createPost(Request $request, ForumRepository $fRepository): Response
    {
        $datas = new ParameterBag($this->serializer->decode($request->getContent(), 'json'));
        foreach (['titre', 'utilisateur', 'forum'] as $field) {
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
            ->setForum($forum)
        ;

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        return $this->json(['message' => 'Post créé avec succès']);
    }

    #[Route('/{post<\d*>}', name: '_update_post', methods: ['PUT'])]
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

            return $this->json(['message' => 'Post mis à jour avec succès']);
        } catch (ORMException $orm) {
            return $this->json(['error' => $orm->getMessage()], 500);
        }
    }
}
