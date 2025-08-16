<?php

namespace App\Controller;

use App\DTO\MessageResponseDTO;
use App\Entity\{Message, Post};
use App\Repository\{MessageRepository, UtilisateurRepository};
use App\Service\{AuthenticatedUserService, HttpOnlyCookieService, InitSerializerService, JWTService};
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{ParameterBag, Request, Response};
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Serializer;

#[Route('/api/messages', name: 'app_messages')]
#[OA\Tag(name: 'Message', description: 'Gestion des messages')]
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
    #[OA\Get(
        path: '/api/messages/{post}',
        summary: 'Liste des messages d\'un post',
        description: 'Retourne la liste des messages pour un post donné',
        tags: ['Message'],
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
                description: 'Liste des messages',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        ref: new Model(type: MessageResponseDTO::class)
                    )
                )
            ),
        ]
    )]
    public function loadMessages(Post $post): Response
    {
        $messages = $post->getMessages();
        $dtos = array_map(fn ($msg) => MessageResponseDTO::fromEntity($msg), $messages->toArray());

        return $this->json($this->serializer->normalize($dtos, 'json'));
    }

    #[Route('/{post<\d*>}', name: '_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/messages/{post}',
        summary: 'Créer un message',
        description: 'Crée un message pour un post donné',
        tags: ['Message'],
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
            description: 'Contenu du message',
            content: new OA\JsonContent(
                required: ['text', 'utilisateurId'],
                properties: [
                    new OA\Property(property: 'text', type: 'string'),
                    new OA\Property(property: 'utilisateurId', type: 'integer'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Message créé',
                content: new OA\JsonContent(
                    ref: new Model(type: MessageResponseDTO::class)
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Paramètres manquants ou invalides'
            ),
        ]
    )]
    public function createMessage(Post $post, Request $request, MessageRepository $mRepository, AuthenticatedUserService $authenticatedUserService): Response
    {
        $datas = new ParameterBag($this->serializer->decode($request->getContent(), 'json'));
        foreach (['text'] as $value) {
            if (!$datas->has($value)) {
                return $this->json(['error' => "Missing parameter: {$value}"], Response::HTTP_BAD_REQUEST);
            }
        }

        $message = new Message();
        $message
            ->setText($datas->get('text'))
            ->setPost($post)
        ;

        [$user, $error] = $authenticatedUserService->getAuthenticatedUser($request);
        if ($error) {
            return $this->json($error->toArray(), 401);
        }

        $message->setUtilisateur($user);

        try {
            $this->entityManager->persist($message);
            $this->entityManager->flush();
            $dto = MessageResponseDTO::fromEntity($message);

            return $this->json($this->serializer->normalize($dto, 'json'), Response::HTTP_CREATED);
        } catch (ORMException $orm) {
            return $this->json(['error' => $orm->getMessage()], 404);
        }
    }

    #[Route('/{message<\d*>}', name: '_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/messages/{message}',
        summary: 'Supprimer un message',
        description: 'Supprime un message par son ID',
        tags: ['Message'],
        parameters: [
            new OA\Parameter(
                name: 'message',
                in: 'path',
                required: true,
                description: 'ID du message',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Message supprimé avec succès'
            ),
            new OA\Response(
                response: 404,
                description: 'Message non trouvé'
            ),
        ]
    )]
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

    #[Route('/all', name: '_all', methods: ['GET'])]
    #[OA\Get(
        path: '/api/messages/all',
        summary: 'Liste de tous les messages',
        description: 'Retourne la liste de tous les messages',
        tags: ['Message'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des messages',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        ref: new Model(type: MessageResponseDTO::class)
                    )
                )
            ),
        ]
    )]
    public function getAllMessages(): Response
    {
        $messages = $this->entityManager->getRepository(Message::class)->findAll();
        $dtos = array_map(fn ($message) => MessageResponseDTO::fromEntity($message), $messages);

        return $this->json($this->serializer->normalize($dtos, 'json'));
    }
}
