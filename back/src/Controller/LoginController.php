<?php

namespace App\Controller;

use App\Services\InitSerializerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Serializer\Serializer;

#[Route('/api/login', name: 'app_login')]
final class LoginController extends AbstractController
{
    private Serializer $serializer;

    public function __construct()
    {
        $init = new InitSerializerService();
        $this->serializer = $init->serializer;
    }


    #[Route('', name: '_log', methods: ['POST', 'OPTIONS'])]
    #[IsCsrfTokenValid('authenticate', tokenKey: 'X-CSRF-Token', methods: ['POST'])]
    public function index(Request $request): Response
    {
        $data = new ParameterBag($this->serializer->normalize(json_decode($request->getContent()), 'json'));
        foreach (['login', 'password'] as $key) {
            if (!$data->has($key)) {
                return $this->json(['error' => "Missing parameter: $key"], Response::HTTP_BAD_REQUEST);
            }
        }
        

        dd($data->all());

        return $this->json([]);
    }
}
