<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:logs:search',
    description: 'Recherche dans les logs applicatifs.'
)]
class LogsSearchCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('level', null, InputOption::VALUE_OPTIONAL, 'Niveau de log (error, warning, info, etc.)')
            ->addOption('since', null, InputOption::VALUE_OPTIONAL, 'Depuis quand (ex: "1 hour ago")');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $level = $input->getOption('level');
        $since = $input->getOption('since');
        $output->writeln("Recherche de logs niveau: {$level}, depuis: {$since}");

        $logFile = __DIR__ . '/../../var/log/dev.log';
        if (!file_exists($logFile)) {
            $output->writeln('<error>Log dev.log introuvable.</error>');

            return Command::FAILURE;
        }
        $lines = file($logFile);
        $results = [];
        $sinceTimestamp = null;
        if ($since) {
            $sinceDate = strtotime($since);
            if ($sinceDate !== false) {
                $sinceTimestamp = $sinceDate;
            }
        }
        foreach ($lines as $line) {
            // Filtre par niveau
            if ($level && stripos($line, ".{$level}:") === false) {
                continue;
            }
            // Filtre par date
            if ($sinceTimestamp) {
                if (preg_match('/\[(.*?)\]/', $line, $tsMatch)) {
                    $logDate = strtotime($tsMatch[1]);
                    if ($logDate < $sinceTimestamp) {
                        continue;
                    }
                }
            }
            $results[] = trim($line);
        }
        if (empty($results)) {
            $output->writeln('- Aucun log trouvé pour les critères donnés.');

            return Command::SUCCESS;
        }
        $output->writeln('- ' . count($results) . ' log(s) trouvé(s) :');
        foreach (array_slice($results, -20) as $log) {
            $output->writeln($log);
        }
        if (count($results) > 20) {
            $output->writeln('- ... (seuls les 20 derniers logs sont affichés)');
        }

        return Command::SUCCESS;
    }
}
