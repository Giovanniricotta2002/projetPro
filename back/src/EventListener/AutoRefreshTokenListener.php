<?php

namespace App\EventListener;

use App\Service\HttpOnlyCookieService;
use App\Service\JWTService;
use App\Repository\UtilisateurRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Middleware pour le refresh automatique des tokens JWT expirés.
 * 
 * Intercepte les requêtes avec des access tokens expirés et tente
 * automatiquement de les rafraîchir avec le refresh token.
 */
class AutoRefreshTokenListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly JWTService $jwtService,
        private readonly HttpOnlyCookieService $cookieService,
        private readonly UtilisateurRepository $userRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10], // Priorité élevée
            KernelEvents::RESPONSE => ['onKernelResponse', -10], // Après la réponse
        ];
    }

    /**
     * Intercepte les requêtes pour vérifier et rafraîchir les tokens si nécessaire.
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        
        // Ne traiter que les routes API protégées
        if (!str_starts_with($request->getPathInfo(), '/api/') || 
            in_array($request->getPathInfo(), ['/api/login', '/api/tokens/refresh', '/api/tokens/logout'])) {
            return;
        }

        $accessToken = $this->cookieService->extractAccessToken($request);
        
        if (!$accessToken) {
            return; // Pas de token, laisser le contrôleur gérer
        }

        try {
            // Vérifier si le token est valide
            $this->jwtService->validateToken($accessToken);
            return; // Token valide, continuer normalement
        } catch (\Exception $e) {
            // Token invalide/expiré, tenter le refresh
            $this->attemptTokenRefresh($event);
        }
    }

    /**
     * Tente de rafraîchir automatiquement le token expiré.
     */
    private function attemptTokenRefresh(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $refreshToken = $this->cookieService->extractRefreshToken($request);

        if (!$refreshToken) {
            // Pas de refresh token, retourner 401
            $response = new JsonResponse(
                ['error' => 'Access token expired and no refresh token available'],
                Response::HTTP_UNAUTHORIZED
            );
            $event->setResponse($response);
            return;
        }

        try {
            // Valider le refresh token
            $tokenPayload = $this->jwtService->validateToken($refreshToken);
            
            if (!$this->jwtService->isTokenType($tokenPayload, 'refresh')) {
                throw new \InvalidArgumentException('Invalid refresh token type');
            }

            // Récupérer l'utilisateur
            $userId = (int) $tokenPayload['sub'];
            $user = $this->userRepository->find($userId);

            if (!$user) {
                throw new \InvalidArgumentException('User not found');
            }

            // Générer de nouveaux tokens
            $newTokens = $this->jwtService->generateTokenPair($user, [
                'auto_refreshed' => true,
                'refresh_time' => time(),
                'ip_address' => $request->getClientIp(),
                'user_agent' => $request->headers->get('User-Agent'),
            ]);

            // Stocker les nouveaux tokens pour les ajouter à la réponse
            $request->attributes->set('_refreshed_tokens', $newTokens);
            $request->attributes->set('_new_access_token', $newTokens['access_token']);

        } catch (\Exception $e) {
            // Refresh échoué, retourner 401
            $response = new JsonResponse(
                ['error' => 'Token refresh failed: ' . $e->getMessage()],
                Response::HTTP_UNAUTHORIZED
            );
            $event->setResponse($response);
        }
    }

    /**
     * Ajoute les nouveaux tokens aux cookies de la réponse si un refresh a eu lieu.
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        // Vérifier si des tokens ont été rafraîchis
        $refreshedTokens = $request->attributes->get('_refreshed_tokens');
        
        if ($refreshedTokens) {
            // Ajouter les nouveaux cookies à la réponse
            $this->cookieService->setJwtCookies($response, $request, $refreshedTokens);
            
            // Ajouter un header pour indiquer que le token a été rafraîchi
            $response->headers->set('X-Token-Refreshed', 'true');
        }
    }
}
