<?php

namespace App\Controller;

use App\DTO\{MachineCreateRequestDTO, MachineResponseDTO, MachineUpdateRequestDTO};
use App\Entity\{InfoMachine, Machine};
use App\Repository\{MachineRepository, UtilisateurRepository};
use App\Service\{HttpOnlyCookieService, InitSerializerService, JWTService};
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\{JsonResponse, ParameterBag, Request, Response};
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/machines', name: 'app_machines')]
final class MachinesController extends AbstractController
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

    #[Route('/', name: '_create_machine', methods: ['POST'])]
    #[OA\Post(
        summary: 'Créer une machine',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: MachineCreateRequestDTO::class))
        ),
        responses: [
            new OA\Response(response: 201, description: 'Machine créée'),
            new OA\Response(response: 400, description: 'Erreur de validation'),
        ]
    )]
    public function create(
        Request $request,
        ValidatorInterface $validator,
        ParameterBagInterface $bag,
    ): JsonResponse {
        $data = new ParameterBag($this->serializer->decode($request->getContent(), 'json'));
        foreach (['nom', 'image', 'infoMachines'] as $field) {
            if (!$data->has($field)) {
                return $this->json([
                    'success' => false,
                    'message' => "Le champ '{$field}' est requis.",
                ], 400);
            }
        }

        // $errors = $validator->validate($dto);
        // if (count($errors) > 0) {
        //     return $this->json([
        //         'success' => false,
        //         'message' => (string) $errors,
        //     ], 400);
        // }

        $machine = new Machine();
        $machine
            ->setName($data->get('nom'))
            ->setImage($data->get('image'))
            ->setDescription($data->get('description'))
        ;

        foreach ($data->get('infoMachines') as $infoMachine) {
            $im = new InfoMachine();
            $im
                ->setText($infoMachine['text'])
                ->setType($infoMachine['type'])
            ;
            try {
                $this->entityManager->persist($im);
                $this->entityManager->flush();
            } catch (ORMException $orm) {
                return $this->json($orm->getMessage(), 404);
            }

            $machine->addInfoMachine($im);
        }

        try {
            $this->entityManager->persist($machine);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'data' => [
                    'id' => $machine->getId(),
                ],
            ], 201);
        } catch (ORMException $orm) {
            return $this->json($orm->getMessage(), 404);
        }
    }

    #[Route('/', name: '_all_machines', methods: ['GET'])]
    #[OA\Get(
        path: '/api/machines/',
        summary: 'Liste des machines',
        description: 'Retourne la liste de toutes les machines',
        tags: ['Machine'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des machines',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        ref: new Model(type: MachineResponseDTO::class)
                    )
                )
            ),
        ]
    )]
    public function getAllMachines(MachineRepository $mRepository): Response
    {
        $machines = $mRepository->findAll();
        $dtos = array_map(fn ($machine) => MachineResponseDTO::fromEntity($machine), $machines);

        return $this->json($this->serializer->normalize($dtos, 'json'));
    }

    #[Route('/{materielId}', name: '_machine_by_id', methods: ['GET'])]
    #[OA\Get(
        path: '/api/machines/{materielId}',
        summary: 'Récupérer une machine',
        description: 'Retourne les informations d\'une machine par son ID',
        tags: ['Machine'],
        parameters: [
            new OA\Parameter(
                name: 'materielId',
                in: 'path',
                required: true,
                description: 'ID de la machine',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Données de la machine',
                content: new OA\JsonContent(
                    ref: new Model(type: MachineResponseDTO::class)
                )
            ),
        ]
    )]
    public function getMachineById(Machine $materielId): Response
    {
        $dto = MachineResponseDTO::fromEntity($materielId);

        return $this->json($this->serializer->normalize($dto, 'json'));
    }

    #[Route('/{materielId}', name: '_update_machine', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/machines/{materielId}',
        summary: 'Mettre à jour une machine',
        description: 'Met à jour une machine existante',
        tags: ['Machine'],
        parameters: [
            new OA\Parameter(
                name: 'materielId',
                in: 'path',
                required: true,
                description: 'ID de la machine',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: MachineUpdateRequestDTO::class))
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Machine mise à jour',
                content: new OA\JsonContent(
                    ref: new Model(type: MachineResponseDTO::class)
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
            new OA\Response(
                response: 404,
                description: 'Erreur lors de la mise à jour',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string'),
                    ]
                )
            ),
        ]
    )]
    public function updateMachine(
        Request $request,
        Machine $materielId,
    ): Response {
        $data = new ParameterBag($this->serializer->decode($request->getContent(), 'json'));
        foreach (['nom', 'image', 'infoMachines', 'description'] as $field) {
            if (!$data->has($field)) {
                return $this->json([
                    'success' => false,
                    'message' => "Le champ '{$field}' est requis.",
                ], 400);
            }
        }
        $materielId
            ->setName($data->get('nom'))
            ->setImage($data->get('image'))
            ->setDescription($data->get('description'))
            ->setDateModif(new \DateTime())
        ;
        foreach ($data->get('infoMachines') as $infoMachine) {
            $infoM = $this->serializer->denormalize($infoMachine, InfoMachine::class);
            if ($materielId->getInfoMachines()->contains($infoM)) {
                /**
                 * @var InfoMachine
                 */
                $im = $materielId->getInfoMachines()->get($infoM);
                if ($im->getText() !== $infoMachine['text']) {
                    $im->setText($infoMachine['text']);
                }

                if ($im->getType() !== $infoMachine['type']) {
                    $im->setType($infoMachine['type']);
                }
                try {
                    $this->entityManager->persist($im);
                    $this->entityManager->flush();
                } catch (ORMException $orm) {
                    return $this->json($orm->getMessage(), 404);
                }
            } elseif ($infoMachine['remove']) {
                $materielId->removeInfoMachine($infoM);

                try {
                    $this->entityManager->persist($materielId);
                    $this->entityManager->flush();
                } catch (ORMException $orm) {
                    return $this->json($orm->getMessage(), 404);
                }
            } else {
                $im = new InfoMachine();
                $im
                    ->setText($infoMachine['text'])
                    ->setType($infoMachine['type'])
                ;

                try {
                    $this->entityManager->persist($im);
                    $this->entityManager->flush();
                } catch (ORMException $orm) {
                    return $this->json($orm->getMessage(), 404);
                }
                $materielId->addInfoMachine($im);
            }
        }

        try {
            $this->entityManager->persist($materielId);
            $this->entityManager->flush();
            $dto = MachineResponseDTO::fromEntity($materielId);

            return $this->json($this->serializer->normalize($dto, 'json'));
        } catch (ORMException $orm) {
            return $this->json(['error' => $orm->getMessage()], 404);
        }
    }
}
