<?php

namespace App\EventListener;

use App\Attribute\LogLogin;
use App\DTO\PendingLoginLogDTO;
use App\Service\LoginLoggerService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event listener qui gère automatiquement le logging des tentatives de connexion
 * basé sur l'attribut #[LogLogin] appliqué aux méthodes de contrôleur.
 *
 * Ce listener intercepte les requêtes vers les méthodes annotées avec #[LogLogin],
 * vérifie les blocages IP/login, et logue automatiquement les tentatives de connexion
 * après traitement de la réponse.
 *
 * @author Ricotta Giovanni
 *
 * @since 1.0.0
 */
class LogLoginAttributeListener
{
    /**
     * Stockage temporaire des informations de logging en attente de traitement.
     * Clé : hash de l'objet Request, Valeur : DTO contenant les informations de logging.
     *
     * @var array<string, PendingLoginLogDTO>
     */
    private array $pendingLogs = [];

    /**
     * Constructeur de l'event listener.
     *
     * @param LoginLoggerService $loginLogger Service de logging des connexions
     */
    public function __construct(
        private readonly LoginLoggerService $loginLogger,
    ) {
    }

    /**
     * Intercepte les requêtes vers les contrôleurs annotés avec #[LogLogin].
     *
     * Vérifie la présence de l'attribut LogLogin sur la méthode ou la classe du contrôleur.
     * Si trouvé et activé, analyse les données de connexion, effectue les vérifications
     * de blocage IP/login et stocke les informations pour le logging post-réponse.
     *
     * En cas de blocage détecté, remplace le contrôleur par une réponse d'erreur 429.
     *
     * @param ControllerEvent $event Événement contenant les informations du contrôleur
     *
     * @throws \ReflectionException Si la réflection échoue
     * @throws \JsonException       Si le JSON de la requête est malformé
     */
    #[AsEventListener(event: KernelEvents::CONTROLLER)]
    public function onController(ControllerEvent $event): void
    {
        $controller = $event->getController();

        // Vérifier que le contrôleur est dans le format attendu [objet, méthode]
        if (!is_array($controller)) {
            return;
        }

        // Rechercher l'attribut LogLogin sur la méthode du contrôleur
        $reflectionMethod = new \ReflectionMethod($controller[0], $controller[1]);
        $attributes = $reflectionMethod->getAttributes(LogLogin::class);

        if (empty($attributes)) {
            // Si pas trouvé sur la méthode, vérifier sur la classe
            $reflectionClass = new \ReflectionClass($controller[0]);
            $attributes = $reflectionClass->getAttributes(LogLogin::class);
        }

        // Aucun attribut LogLogin trouvé
        if (empty($attributes)) {
            return;
        }

        /** @var LogLogin $logLoginAttribute */
        $logLoginAttribute = $attributes[0]->newInstance();

        // L'attribut est désactivé
        if (!$logLoginAttribute->enabled) {
            return;
        }

        $request = $event->getRequest();

        // Seules les requêtes POST sont traitées
        if ($request->getMethod() !== 'POST') {
            return;
        }

        $content = $request->getContent();
        if (empty($content)) {
            return;
        }

        try {
            $data = json_decode($content, true);
            if (!$data) {
                return;
            }

            // Extraire les credentials selon la configuration de l'attribut
            $parameterBag = new ParameterBag($data);
            $username = $parameterBag->get($logLoginAttribute->usernameField);
            $password = $parameterBag->get($logLoginAttribute->passwordField);

            if (!$username || !$password) {
                return;
            }

            // Vérifications de blocage si activées dans l'attribut
            if ($logLoginAttribute->checkBlocking) {
                $clientIp = $request->getClientIp();

                // Vérifier si l'IP est bloquée
                if (
                    $this->loginLogger->isIpBlocked(
                        $clientIp,
                        $logLoginAttribute->maxIpAttempts,
                        $logLoginAttribute->ipBlockDuration
                    )
                ) {
                    // Logger l'échec et retourner une erreur 429
                    $this->loginLogger->logFailedLogin($username);

                    $response = new JsonResponse([
                        'error' => 'Too many failed attempts. IP temporarily blocked.',
                        'retry_after' => $logLoginAttribute->ipBlockDuration * 60,
                    ], Response::HTTP_TOO_MANY_REQUESTS);

                    // Remplacer le contrôleur par cette réponse directe
                    $event->setController(function () use ($response) {
                        return $response;
                    });

                    return;
                }

                // Vérifier si le login est bloqué
                if (
                    $this->loginLogger->isLoginBlocked(
                        $username,
                        $logLoginAttribute->maxLoginAttempts,
                        $logLoginAttribute->loginBlockDuration
                    )
                ) {
                    // Logger l'échec et retourner une erreur 429
                    $this->loginLogger->logFailedLogin($username);

                    $response = new JsonResponse([
                        'error' => 'Too many failed attempts for this account. Temporarily blocked.',
                        'retry_after' => $logLoginAttribute->loginBlockDuration * 60,
                    ], Response::HTTP_TOO_MANY_REQUESTS);

                    // Remplacer le contrôleur par cette réponse directe
                    $event->setController(function () use ($response) {
                        return $response;
                    });

                    return;
                }
            }

            // Stocker les informations pour le traitement post-réponse
            $requestId = spl_object_hash($request);
            $this->pendingLogs[$requestId] = PendingLoginLogDTO::create(
                $username,
                $logLoginAttribute
            );
        } catch (\Exception $e) {
            // En cas d'erreur, ne pas bloquer le processus normal
            error_log('LogLoginAttribute error: ' . $e->getMessage());
        }
    }

    /**
     * Traite les réponses des contrôleurs pour effectuer le logging automatique.
     *
     * Récupère les informations stockées lors de l'événement CONTROLLER,
     * détermine le succès/échec de la connexion basé sur le code de statut HTTP,
     * applique les filtres de l'attribut LogLogin et effectue le logging.
     *
     * Ajoute des headers informatifs à la réponse et nettoie les données temporaires.
     *
     * @param ResponseEvent $event Événement contenant la requête et la réponse
     */
    #[AsEventListener(event: KernelEvents::RESPONSE)]
    public function onResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        $requestId = spl_object_hash($request);

        // Vérifier si des informations de logging sont en attente pour cette requête
        if (!isset($this->pendingLogs[$requestId])) {
            return;
        }

        $pendingLog = $this->pendingLogs[$requestId];

        // Déterminer le succès/échec basé sur le code de statut HTTP (2xx = succès)
        $isSuccess = $response->getStatusCode() >= 200 && $response->getStatusCode() < 300;

        // Effectuer le logging si autorisé par les filtres du DTO
        if ($pendingLog->shouldLog($isSuccess)) {
            try {
                $this->loginLogger->logLoginAttempt(
                    $pendingLog->username,
                    $isSuccess,
                    $pendingLog->requestTime
                );

                // Ajouter des headers informatifs pour le debugging/monitoring
                if ($response instanceof JsonResponse) {
                    $response->headers->set('X-Login-Logged', 'true');
                    $response->headers->set('X-Login-Status', $isSuccess ? 'success' : 'failure');
                }
            } catch (\Exception $e) {
                // Logger l'erreur sans interrompre la réponse normale
                error_log('Failed to log login attempt: ' . $e->getMessage());
            }
        }

        // Nettoyer les données temporaires pour éviter les fuites mémoire
        unset($this->pendingLogs[$requestId]);
    }
}
