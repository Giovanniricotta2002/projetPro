<?php

namespace App\Attribute;

/**
 * Attribut pour automatiser le logging des tentatives de connexion.
 *
 * Cet attribut peut être appliqué sur des méthodes ou des classes de contrôleur
 * pour activer automatiquement le logging des tentatives de connexion, avec
 * des fonctionnalités de blocage par IP ou par login en cas d'échecs répétés.
 *
 * @example
 * ```php
 * #[LogLogin(
 *     enabled: true,
 *     usernameField: 'email',
 *     maxIpAttempts: 10,
 *     ipBlockDuration: 120
 * )]
 * public function login(Request $request): Response
 * {
 *     // Votre logique de connexion
 * }
 * ```
 *
 * @author Votre nom
 *
 * @since 1.0.0
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS)]
class LogLogin
{
    /**
     * Constructeur de l'attribut LogLogin.
     *
     * @param bool   $enabled            Active ou désactive le logging pour cette méthode/classe
     * @param bool   $logSuccessOnly     Si true, ne logue que les connexions réussies
     * @param bool   $logFailureOnly     Si true, ne logue que les connexions échouées
     * @param string $usernameField      Nom du champ contenant le nom d'utilisateur dans la requête JSON
     * @param string $passwordField      Nom du champ contenant le mot de passe dans la requête JSON
     * @param bool   $checkBlocking      Active ou désactive les vérifications de blocage automatique
     * @param int    $maxIpAttempts      Nombre maximum de tentatives échouées par IP avant blocage
     * @param int    $maxLoginAttempts   Nombre maximum de tentatives échouées par login avant blocage
     * @param int    $ipBlockDuration    Durée de blocage IP en minutes
     * @param int    $loginBlockDuration Durée de blocage login en minutes
     */
    public function __construct(
        public readonly bool $enabled = true,
        public readonly bool $logSuccessOnly = false,
        public readonly bool $logFailureOnly = false,
        public readonly string $usernameField = 'login',
        public readonly string $passwordField = 'password',
        public readonly bool $checkBlocking = true,
        public readonly int $maxIpAttempts = 5,
        public readonly int $maxLoginAttempts = 3,
        public readonly int $ipBlockDuration = 60,
        public readonly int $loginBlockDuration = 30,
    ) {
    }
}
