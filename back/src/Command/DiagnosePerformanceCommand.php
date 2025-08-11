<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputInterface, InputOption};
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour diagnostiquer les performances d'une route API spécifique.
 *
 * Cette commande lit les logs Symfony pour extraire les appels à une route donnée,
 * affiche le nombre d'appels et les derniers timestamps associés.
 *
 * @example
 *   php bin/console app:diagnose:performance --route=api_login
 *
 * @author Giovanni Ricotta
 *
 * @since 1.0.0
 */
#[AsCommand(
    name: 'app:diagnose:performance',
    description: 'Affiche des métriques de performance pour une route API.'
)]
class DiagnosePerformanceCommand extends Command
{
    /**
     * Configure les options de la commande.
     */
    protected function configure(): void
    {
        $this->addOption('route', null, InputOption::VALUE_REQUIRED, 'Route API à diagnostiquer');
    }

    /**
     * Exécute la commande de diagnostic de performance.
     *
     * @param InputInterface  $input  Interface d'entrée de la commande
     * @param OutputInterface $output Interface de sortie de la commande
     *
     * @return int Code de statut de la commande (SUCCESS ou FAILURE)
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $route = $input->getOption('route');
        $output->writeln("Diagnostic de performance pour la route : {$route}");

        // Lecture du log Symfony pour extraire les temps de réponse de la route
        $logFile = __DIR__ . '/../../var/log/dev.log';
        if (!file_exists($logFile)) {
            $output->writeln('<error>Log dev.log introuvable.</error>');

            return Command::FAILURE;
        }
        $lines = file($logFile);
        $pattern = '/Matched route "' . preg_quote($route, '/') . '".*request_uri":"([^"]+)".*method":"([^"]+)"/';
        $count = 0;
        $timestamps = [];
        foreach ($lines as $line) {
            if (preg_match($pattern, $line, $matches)) {
                // Cherche le timestamp du log
                if (preg_match('/\[(.*?)\]/', $line, $tsMatch)) {
                    $timestamps[] = $tsMatch[1];
                    ++$count;
                }
            }
        }
        if ($count === 0) {
            $output->writeln("- Aucune trace trouvée pour la route '{$route}' dans les logs.");

            return Command::SUCCESS;
        }
        $output->writeln("- Nombre d'appels trouvés : {$count}");
        $output->writeln('- Timestamps des derniers appels :');
        foreach (array_slice($timestamps, -5) as $ts) {
            $output->writeln("  - {$ts}");
        }
        $output->writeln('- (Astuce : Pour des métriques avancées, activez le profiler Symfony ou utilisez un APM comme Blackfire/Grafana.)');

        return Command::SUCCESS;
    }
}
