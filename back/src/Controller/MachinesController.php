<?php

namespace App\Controller;

use App\Entity\InfoMachine;
use App\Entity\Machine;
use App\Repository\MachineRepository;
use App\Repository\UtilisateurRepository;
use App\Service\HttpOnlyCookieService;
use App\Service\InitSerializerService;
use App\Service\JWTService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
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

    public function index(): Response
    {
        return $this->render('machines/index.html.twig', [
            'controller_name' => 'MachinesController',
        ]);
    }

    #[Route('/', name: '_create_machine', methods: ['POST'])]
    #[OA\Post(
        summary: 'Créer une machine',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nom', type: 'string'),
                    new OA\Property(property: 'image', type: 'string'),
                    new OA\Property(property: 'infoMachines', type: 'string'),
                ]
            )
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
    public function getAllMachines(MachineRepository $mRepository): Response
    {
        $machines = $mRepository->findAll();

        $context = [
            AbstractNormalizer::ATTRIBUTES => [
                'id',
                // 'uuid',
                'name',
                'visible',
                'image',
                'description',
                'forum' => ['id'],
                'infoMachines' => ['id'],
            ],
        ];

        return $this->json($this->serializer->normalize($machines, 'json', $context));
    }

    #[Route('/{materielId}', name: '_machine_by_id', methods: ['GET'])]
    public function getMachineById(Machine $materielId): Response
    {
        $context = [
            AbstractNormalizer::ATTRIBUTES => [
                'id',
                // 'uuid',
                'name',
                'dateModif',
                'visible',
                'image',
                'description',
                'forum' => ['id'],
                'infoMachines' => ['id', 'text', 'type'],
            ],
        ];

        return $this->json($this->serializer->normalize($materielId, 'json', $context));
    }

    #[Route('/{materielId}', name: '_update_machine', methods: ['PUT'])]
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
            dd($infoM, $materielId->getInfoMachines()->contains($infoM), $infoMachine);
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
            }

            $materielId->addInfoMachine($im);
        }

        try {
            $this->entityManager->persist($materielId);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'data' => [
                    'id' => $materielId->getId(),
                ],
            ], 200);
        } catch (ORMException $orm) {
            return $this->json($orm->getMessage(), 404);
        }
    }
}
