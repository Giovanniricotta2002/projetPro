<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Service pour la gestion des cookies httpOnly sécurisés pour les tokens JWT.
 */
class HttpOnlyCookieService
{
    public const ACCESS_TOKEN_COOKIE = 'access_token';
    public const REFRESH_TOKEN_COOKIE = 'refresh_token';
    
    private const ACCESS_TOKEN_TTL = 3600; // 1 heure
    private const REFRESH_TOKEN_TTL = 604800; // 7 jours
    private const REFRESH_TOKEN_PATH = '/api/tokens/refresh';

    /**
     * Définit les cookies httpOnly pour les tokens JWT sur la réponse.
     */
    public function setJwtCookies(Response $response, Request $request, array $tokens): void
    {
        // Cookie pour l'access token
        $response->headers->setCookie(
            $this->createJwtCookie(
                name: self::ACCESS_TOKEN_COOKIE,
                value: $tokens['access_token'],
                ttl: self::ACCESS_TOKEN_TTL,
                path: '/',
                request: $request
            )
        );

        // Cookie pour le refresh token (avec chemin restreint)
        $response->headers->setCookie(
            $this->createJwtCookie(
                name: self::REFRESH_TOKEN_COOKIE,
                value: $tokens['refresh_token'],
                ttl: self::REFRESH_TOKEN_TTL,
                path: self::REFRESH_TOKEN_PATH,
                request: $request
            )
        );
    }

    /**
     * Supprime les cookies JWT en définissant une date d'expiration dans le passé.
     */
    public function clearJwtCookies(Response $response, Request $request): void
    {
        // Supprimer le cookie access_token
        $response->headers->setCookie(
            $this->createExpiredCookie(
                name: self::ACCESS_TOKEN_COOKIE,
                path: '/',
                request: $request
            )
        );

        // Supprimer le cookie refresh_token
        $response->headers->setCookie(
            $this->createExpiredCookie(
                name: self::REFRESH_TOKEN_COOKIE,
                path: self::REFRESH_TOKEN_PATH,
                request: $request
            )
        );
    }

    /**
     * Extrait l'access token depuis les cookies httpOnly ou les headers Authorization.
     * Priorité : Cookie > Header Authorization
     */
    public function extractAccessToken(Request $request): ?string
    {
        // Priorité 1 : Cookie httpOnly (plus sécurisé)
        if ($request->cookies->has(self::ACCESS_TOKEN_COOKIE)) {
            return $request->cookies->get(self::ACCESS_TOKEN_COOKIE);
        }

        // Priorité 2 : Header Authorization (fallback pour les API clients)
        $authHeader = $request->headers->get('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }

        return null;
    }

    /**
     * Extrait le refresh token depuis les cookies httpOnly ou le body de la requête.
     * Priorité : Cookie > Body JSON
     */
    public function extractRefreshToken(Request $request): ?string
    {
        // Priorité 1 : Cookie httpOnly (plus sécurisé)
        if ($request->cookies->has(self::REFRESH_TOKEN_COOKIE)) {
            return $request->cookies->get(self::REFRESH_TOKEN_COOKIE);
        }

        // Priorité 2 : Body de la requête (fallback pour les API clients)
        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            return $data['refresh_token'] ?? null;
        } catch (\JsonException) {
            return null;
        }
    }

    /**
     * Crée un cookie JWT sécurisé.
     */
    private function createJwtCookie(
        string $name,
        string $value,
        int $ttl,
        string $path,
        Request $request
    ): Cookie {
        return new Cookie(
            name: $name,
            value: $value,
            expire: time() + $ttl,
            path: $path,
            domain: null,
            secure: $request->isSecure(), // HTTPS uniquement en production
            httpOnly: true, // Protection XSS - inaccessible via JavaScript
            raw: false,
            sameSite: Cookie::SAMESITE_STRICT // Protection CSRF
        );
    }

    /**
     * Crée un cookie expiré pour supprimer un cookie existant.
     */
    private function createExpiredCookie(string $name, string $path, Request $request): Cookie
    {
        return new Cookie(
            name: $name,
            value: '',
            expire: time() - 3600, // Expiration dans le passé
            path: $path,
            domain: null,
            secure: $request->isSecure(),
            httpOnly: true,
            raw: false,
            sameSite: Cookie::SAMESITE_STRICT
        );
    }

    /**
     * Vérifie si la requête contient des cookies JWT.
     */
    public function hasJwtCookies(Request $request): bool
    {
        return $request->cookies->has(self::ACCESS_TOKEN_COOKIE) 
            || $request->cookies->has(self::REFRESH_TOKEN_COOKIE);
    }

    /**
     * Retourne les TTL configurés pour les tokens.
     */
    public function getTokenTtls(): array
    {
        return [
            'access_token_ttl' => self::ACCESS_TOKEN_TTL,
            'refresh_token_ttl' => self::REFRESH_TOKEN_TTL,
        ];
    }
}
