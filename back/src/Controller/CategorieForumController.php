<?php

namespace App\Controller;

use App\Repository\CategorieForumRepository;
use App\Repository\UtilisateurRepository;
use App\Service\HttpOnlyCookieService;
use App\Service\InitSerializerService;
use App\Service\JWTService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Serializer;

#[Route('/api/categorie-forum', name: 'app_categorie_forum')]
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
    public function loadCategories(CategorieForumRepository $cfRepository): Response
    {
        $categories = $cfRepository->findAll();

        return $this->json($this->serializer->normalize($categories, 'json', [
            AbstractNormalizer::ATTRIBUTES => [
                'id',
                'name',
                'ordre',
            ],
        ]));
    }
}
