<?php

namespace App\Controller;

use App\Entity\Machine;
use App\Repository\MachineRepository;
use App\Security\Voter\MachineVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route('/api/machine', name: 'app_api_machine')]
final class MachineController extends AbstractController
{
    #[Route('/{machine}/edit', name: '_edit', methods: ['PUT'])]
    public function edit(Machine $machine, Request $request, AuthorizationCheckerInterface $authChecker): JsonResponse
    {
        if (!$authChecker->isGranted(MachineVoter::EDIT, $machine)) {
            return $this->json([
                'error' => 'Access Denied',
                'message' => 'You do not have permission to edit this machine.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $this->json([], Response::HTTP_OK);
    }

    #[Route('/', name: '_show', methods: ['GET'])]
    public function show(Request $request, AuthorizationCheckerInterface $authChecker, MachineRepository $mRepository): JsonResponse
    {
        $machines = $mRepository->findAll();
        $data = [];
        foreach ($machines as $mat) {
            $data[] = [
                'id' => $mat->getId(),
                'name' => $mat->getName(),
                'image' => $mat->getImage(),
                'canEdit' => $authChecker->isGranted(MachineVoter::EDIT, $mat),
            ];
        }

        return $this->json($data, Response::HTTP_OK);
    }
}
