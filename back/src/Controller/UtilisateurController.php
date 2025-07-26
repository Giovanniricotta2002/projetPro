<?php

namespace App\Controller;

use App\Entity\Utilisateur;
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

    #[Route('/{utilisateur<\d*>}', name: '_me', methods: ['GET'])]
    public function getUtilisateur(Utilisateur $utilisateur): Response
    {
        //          id: 1,
        //   username: 'johndoe',
        //   roles: ['user'],
        //   dateCreation: '2024-01-01',
        //   anonimus: false,
        //   status: 'active',
        //   mail: 'johndoe@example.com',
        //   lastVisit: '2025-07-19',
        //   forums: [
        //     { id: 1, titre: 'Forum général', ordreAffichage: 1, visible: true, slug: 'forum-general', dateCreation: null, createdAt: null },
        //     { id: 2, titre: 'Matériel', ordreAffichage: 2, visible: true, slug: 'materiel', dateCreation: null, createdAt: null },
        //   ],

        $context = [
            AbstractNormalizer::ATTRIBUTES => [
                'id',
                'username',
                'roles',
                'password',
                'dateCreation',
                'anonimus',
                'mail',
                'status',
                'createdAt',
            ],
        ];

        return $this->json($this->serializer->normalize($utilisateur, 'json', $context));
    }
}
