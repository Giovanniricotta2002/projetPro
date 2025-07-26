<?php

namespace App\Controller;

use App\Entity\Forum;
use App\Repository\CategorieForumRepository;
use App\Repository\ForumRepository;
use App\Repository\UtilisateurRepository;
use App\Service\HttpOnlyCookieService;
use App\Service\InitSerializerService;
use App\Service\JWTService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Serializer;

#[Route('/api/forum', name: 'app_forum')]
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
    public function getForums(ForumRepository $fRepository): Response
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

        $forums = $fRepository->findAll();

        return $this->json($this->serializer->normalize($forums, 'json', $context));
    }

    #[Route('/', name: '_create_forum', methods: ['POST'])]
    public function createForum(Request $request, CategorieForumRepository $cfRepository, UtilisateurRepository $uRepository): Response
    {
        $datas = new ParameterBag($this->serializer->decode($request->getContent(), 'json'));
        foreach (['titre', 'categories', 'description', 'ordreAffichage', 'utilisateur'] as $value) {
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
        // $forum->setSlug($datas->get('slug', ''));
        $forum->setUtilisateur($uRepository->find($datas->get('utilisateur')));

        return $this->json([]);
    }

    #[Route('/{forum<\d*>}', name: '_delete_forum', methods: ['DELETE'])]
    public function deleteForum(Forum $forum): Response
    {
        $forum
            ->setVisible(false)
            ->setDateCloture(new \DateTime())
        ;

        return $this->json(['success' => true]);
    }
}
