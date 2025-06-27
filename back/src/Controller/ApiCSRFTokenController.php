<?php

namespace App\Controller;

use Doctrine\ORM\Query\Parameter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints\Yaml;

#[Route('/api/csrfToken')]
final class ApiCSRFTokenController extends AbstractController
{
    private Serializer $serializer;
    public function __construct()
    {
        $normalizer = [new ObjectNormalizer()];
        $encoders = [new JsonEncode(), new CsvEncoder(), new YamlEncoder(), new XmlEncoder()];

        $this->serializer = new Serializer($normalizer, $encoders);
    }

    #[Route('', name: '_generate', methods: ['GET', 'OPTIONS'])]
    public function generateToken(CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $token = $csrfTokenManager->getToken('authenticate')->getValue();

        return $this->json([
            'csrfToken' => $token,
        ], Response::HTTP_OK);
    }

    #[Route('/verify', name: '_verify', methods: ['POST'])]
    public function verifyToken(Request $request, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $data = new ParameterBag($this->serializer->decode($request->getContent(), 'json'));

        if (!$data->has('csrfToken')) {
            return $this->json(['error' => 'CSRF token is missing'], Response::HTTP_BAD_REQUEST);
        }

        if (!$csrfTokenManager->isTokenValid(new CsrfToken('authenticate', $data->get('csrfToken')))) {
            return $this->json(['error' => 'Invalid CSRF token'], Response::HTTP_FORBIDDEN);
        }

        return $this->json([], Response::HTTP_NO_CONTENT);
    }
}
