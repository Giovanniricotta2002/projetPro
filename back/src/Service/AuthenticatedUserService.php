<?php

namespace App\Service;

use App\DTO\ErrorResponseDTO;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Request;

class AuthenticatedUserService
{
    public function __construct(
        private readonly UtilisateurRepository $userRepository,
        private readonly JWTService $jwtService,
        private readonly HttpOnlyCookieService $cookieService,
    ) {
    }

    /**
     * Récupère l'utilisateur authentifié via le cookie HttpOnly et le JWT.
     *
     * @return array [user|null, errorResponseDTO|null]
     */
    public function getAuthenticatedUser(Request $request): array
    {
        $accessToken = $this->cookieService->extractAccessToken($request);
        $user = null;
        $error = null;
        if ($accessToken) {
            try {
                $payload = $this->jwtService->validateToken($accessToken);
                $userId = $payload['sub'] ?? null;
                if ($userId) {
                    $user = $this->userRepository->find($userId);
                }
            } catch (\Exception $e) {
                $error = ErrorResponseDTO::withMessage('Token invalide ou expiré', $e->getMessage());
            }
        }

        return [$user, $error];
    }
}
