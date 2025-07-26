<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Post;
use App\Repository\MessageRepository;
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

#[Route('/api/messages', name: 'app_messages')]
final class MessageController extends AbstractController
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

    #[Route('/{post<\d*>}', name: '_load', methods: ['GET'])]
    public function loadMessages(Post $post): Response
    {
        $context = [
            AbstractNormalizer::ATTRIBUTES => [
                'id',
                'text',
                'dateCreation',
                'dateModification',
                'visible',
                'utilisateur' => [
                    'id',
                    'username',
                    'email',
                ],
            ],
        ];

        $messages = $post->getMessages();

        return $this->json($this->serializer->normalize($messages, 'json', $context));
    }

    #[Route('/{post<\d*>}', name: '_create', methods: ['POST'])]
    public function createMessage(Post $post, Request $request, MessageRepository $mRepository): Response
    {
        $datas = new ParameterBag($this->serializer->decode($request->getContent(), 'json'));
        foreach (['text', 'utilisateurId'] as $value) {
            if (!$datas->has($value)) {
                return $this->json(['error' => "Missing parameter: {$value}"], Response::HTTP_BAD_REQUEST);
            }
        }

        $utilisateur = $this->userRepository->find($datas->get('utilisateurId'));

        $context = [
            AbstractNormalizer::ATTRIBUTES => [
                'id',
                'text',
                'dateCreation',
                'dateModification',
                'visible',
                'utilisateur' => [
                    'id',
                    'username',
                    'email',
                ],
            ],
        ];

        $message = new Message();
        $message
            ->setText($datas->get('text'))
            ->setUtilisateur($utilisateur)
            ->setPost($post)
        ;

        try {
            $this->entityManager->persist($message);
            $this->entityManager->flush();

            return $this->json($this->serializer->normalize($message, 'json', $context));
        } catch (ORMException $orm) {
            return $this->json(['error' => $orm->getMessage()], 404);
        }
    }

    #[Route('/{message<\d*>}', name: '_delete', methods: ['DELETE'])]
    public function deleteMessage(Message $message): Response
    {
        try {
            $this->entityManager->remove($message);
            $this->entityManager->flush();

            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (ORMException $orm) {
            return $this->json(['error' => $orm->getMessage()], 404);
        }
    }
}
