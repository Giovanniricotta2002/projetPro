<?php

namespace App\Service;

use App\Entity\Utilisateur;
use App\DTO\TokenInfoResponseDTO;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

/**
 * Service de gestion des tokens JWT pour l'authentification.
 *
 * Utilise Firebase JWT pour la génération et validation des tokens.
 * Compatible avec le système de logging automatique #[LogLogin].
 */
class JWTService
{
    private const ALGORITHM = 'HS256';
    private const TOKEN_TYPE = 'JWT';
    private const ISSUER = 'muscuscope-api';

    public function __construct(
        #[Autowire('%env(JWT_SECRET_KEY)%')]
        private readonly string $secretKey,
        private readonly LoggerInterface $logger,
        #[Autowire('%env(int:JWT_TOKEN_TTL)%')]
        private readonly int $tokenTtl = 3600, // 1 heure par défaut
        #[Autowire('%env(int:JWT_REFRESH_TTL)%')]
        private readonly int $refreshTtl = 2592000, // 30 jours par défaut
    ) {
    }

    /**
     * Génère un token JWT d'accès pour un utilisateur.
     *
     * @param Utilisateur $user        L'utilisateur pour lequel générer le token
     * @param array       $extraClaims Claims supplémentaires à inclure dans le token
     *
     * @return string Le token JWT encodé
     */
    public function generateAccessToken(Utilisateur $user, array $extraClaims = []): string
    {
        $now = time();

        $payload = array_merge([
            'iss' => self::ISSUER,                    // Issuer
            'aud' => 'muscuscope-users',              // Audience
            'iat' => $now,                            // Issued at
            'exp' => $now + $this->tokenTtl,          // Expiration
            'nbf' => $now,                            // Not before
            'jti' => uniqid('jwt_', true),            // JWT ID
            'sub' => (string) $user->getId(),         // Subject (user ID)
            'username' => $user->getUsername(),        // Username
            'roles' => $user->getRoles(),             // User roles
            'token_type' => 'access',                 // Type de token
            'login_time' => $now,                     // Temps de connexion
        ], $extraClaims);

        try {
            // Génération du token JWT avec Firebase JWT
            $token = JWT::encode($payload, $this->secretKey, self::ALGORITHM);

            $this->logger->info('JWT access token generated', [
                'user_id' => $user->getId(),
                'username' => $user->getUsername(),
                'token_id' => $payload['jti'],
                'expires_at' => date('Y-m-d H:i:s', $payload['exp']),
            ]);

            return $token;
        } catch (\Exception $e) {
            $this->logger->error('Failed to generate JWT access token', [
                'user_id' => $user->getId(),
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Failed to generate access token', 0, $e);
        }
    }

    /**
     * Génère un token de rafraîchissement.
     *
     * @param Utilisateur $user L'utilisateur pour lequel générer le refresh token
     *
     * @return string Le refresh token JWT encodé
     */
    public function generateRefreshToken(Utilisateur $user): string
    {
        $now = time();

        $payload = [
            'iss' => self::ISSUER,
            'aud' => 'muscuscope-users',
            'iat' => $now,
            'exp' => $now + $this->refreshTtl,
            'nbf' => $now,
            'jti' => uniqid('refresh_', true),
            'sub' => (string) $user->getId(),
            'username' => $user->getUsername(),
            'token_type' => 'refresh',
            'created_at' => $now,
        ];

        try {
            $token = JWT::encode($payload, $this->secretKey, self::ALGORITHM);

            $this->logger->info('JWT refresh token generated', [
                'user_id' => $user->getId(),
                'username' => $user->getUsername(),
                'token_id' => $payload['jti'],
                'expires_at' => date('Y-m-d H:i:s', $payload['exp']),
            ]);

            return $token;
        } catch (\Exception $e) {
            $this->logger->error('Failed to generate JWT refresh token', [
                'user_id' => $user->getId(),
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Failed to generate refresh token', 0, $e);
        }
    }

    /**
     * Valide et décode un token JWT.
     *
     * @param string $token Le token JWT à valider
     *
     * @return array Les données décodées du token
     *
     * @throws \InvalidArgumentException Si le token est invalide
     */
    public function validateToken(string $token): array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, self::ALGORITHM));
            $payload = (array) $decoded;

            $this->logger->debug('JWT token validated successfully', [
                'token_id' => $payload['jti'] ?? 'unknown',
                'user_id' => $payload['sub'] ?? 'unknown',
                'token_type' => $payload['token_type'] ?? 'access',
            ]);

            return $payload;
        } catch (ExpiredException $e) {
            $this->logger->warning('JWT token expired', [
                'token' => substr($token, 0, 20) . '...',
                'error' => $e->getMessage(),
            ]);

            throw new \InvalidArgumentException('Token expired', 401, $e);
        } catch (SignatureInvalidException $e) {
            $this->logger->error('JWT token signature invalid', [
                'token' => substr($token, 0, 20) . '...',
                'error' => $e->getMessage(),
            ]);

            throw new \InvalidArgumentException('Invalid token signature', 401, $e);
        } catch (\Exception $e) {
            $this->logger->error('JWT token validation failed', [
                'token' => substr($token, 0, 20) . '...',
                'error' => $e->getMessage(),
            ]);

            throw new \InvalidArgumentException('Invalid token', 400, $e);
        }
    }

    /**
     * Extrait le token JWT du header Authorization.
     *
     * @param string|null $authorizationHeader Le header Authorization
     *
     * @return string|null Le token JWT ou null si non trouvé
     */
    public function extractTokenFromHeader(?string $authorizationHeader): ?string
    {
        if (!$authorizationHeader) {
            return null;
        }

        // Format attendu: "Bearer <token>"
        if (!str_starts_with($authorizationHeader, 'Bearer ')) {
            return null;
        }

        return substr($authorizationHeader, 7);
    }

    /**
     * Vérifie si un token est du type spécifié.
     *
     * @param array  $tokenPayload Le payload décodé du token
     * @param string $expectedType Le type attendu ('access' ou 'refresh')
     *
     * @return bool True si le token est du bon type
     */
    public function isTokenType(array $tokenPayload, string $expectedType): bool
    {
        return ($tokenPayload['token_type'] ?? 'access') === $expectedType;
    }

    /**
     * Obtient les informations utilisateur depuis un token.
     *
     * @param array $tokenPayload Le payload décodé du token
     *
     * @return array Les informations utilisateur
     */
    public function getUserInfoFromToken(array $tokenPayload): array
    {
        return [
            'id' => (int) ($tokenPayload['sub'] ?? 0),
            'username' => $tokenPayload['username'] ?? '',
            'roles' => $tokenPayload['roles'] ?? ['ROLE_USER'],
            'login_time' => $tokenPayload['login_time'] ?? null,
            'token_id' => $tokenPayload['jti'] ?? null,
        ];
    }

    /**
     * Génère une paire de tokens (access + refresh).
     *
     * @param Utilisateur $user        L'utilisateur
     * @param array       $extraClaims Claims supplémentaires pour le token d'accès
     *
     * @return array ['access_token' => string, 'refresh_token' => string, 'expires_in' => int]
     */
    public function generateTokenPair(Utilisateur $user, array $extraClaims = []): array
    {
        $accessToken = $this->generateAccessToken($user, $extraClaims);
        $refreshToken = $this->generateRefreshToken($user);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => $this->tokenTtl,
            'refresh_expires_in' => $this->refreshTtl,
        ];
    }

    /**
     * Obtient les informations du token (pour debugging/monitoring).
     *
     * @param string $token Le token JWT
     *
     * @return TokenInfoResponseDTO Informations du token
     */
    public function getTokenInfo(string $token): TokenInfoResponseDTO
    {
        try {
            $payload = $this->validateToken($token);
            return TokenInfoResponseDTO::validWithDetails($payload);
        } catch (\Exception $e) {
            return TokenInfoResponseDTO::invalid($e->getMessage());
        }
    }
}
