<?php

namespace App\DTO;

use App\Attribute\LogLogin;

/**
 * DTO représentant les informations de logging en attente de traitement.
 *
 * Stocke temporairement les données nécessaires entre l'événement CONTROLLER
 * et l'événement RESPONSE pour effectuer le logging automatique.
 *
 * Utilise readonly pour garantir l'immutabilité des données pendant
 * le cycle de vie de la requête, évitant ainsi les modifications accidentelles
 * ou malveillantes des informations de logging.
 *
 * @author Votre nom
 *
 * @since 1.0.0
 */
readonly class PendingLoginLogDTO
{
    /**
     * Constructeur du DTO.
     *
     * @param string    $username    Nom d'utilisateur extrait de la requête
     * @param LogLogin  $attribute   Configuration de l'attribut LogLogin
     * @param \DateTime $requestTime Horodatage de la requête
     */
    public function __construct(
        public readonly string $username,
        public readonly LogLogin $attribute,
        public readonly \DateTime $requestTime,
    ) {
    }

    /**
     * Factory method pour créer une instance à partir d'un timestamp actuel.
     *
     * @param string   $username  Nom d'utilisateur
     * @param LogLogin $attribute Configuration de l'attribut
     */
    public static function create(string $username, LogLogin $attribute): self
    {
        return new self($username, $attribute, new \DateTime());
    }

    /**
     * Détermine si le logging doit être effectué selon les filtres de l'attribut.
     *
     * @param bool $isSuccess Indique si la connexion a réussi
     *
     * @return bool True si le logging doit être effectué
     */
    public function shouldLog(bool $isSuccess): bool
    {
        // if ($this->attribute->logSuccessOnly && !$isSuccess) {
        //     return false;
        // }

        // if ($this->attribute->logFailureOnly && $isSuccess) {
        //     return false;
        // }

        // return true;

        return match (true) {
            $this->attribute->logSuccessOnly && !$isSuccess => false,
            $this->attribute->logFailureOnly && $isSuccess => false,
            default => true,
        };
    }
}
