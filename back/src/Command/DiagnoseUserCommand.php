<?php

namespace App\Command;

use App\Repository\LogLoginRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:diagnose:user',
    description: 'Affiche des informations de diagnostic sur un utilisateur.'
)]
class DiagnoseUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UtilisateurRepository $uRepository,
        private readonly LogLoginRepository $llRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('user_id', InputArgument::REQUIRED, 'ID de l\'utilisateur à diagnostiquer');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userId = $input->getArgument('user_id');
        $user = $this->uRepository->find($userId);
        if (!$user) {
            $output->writeln("<error>Utilisateur #{$userId} introuvable.</error>");

            return Command::FAILURE;
        }
        $output->writeln("Diagnostic pour l'utilisateur #{$userId} :");
        $output->writeln("- Nom d'utilisateur : " . $user->getUsername());
        $output->writeln('- Email : ' . $user->getMail());
        $output->writeln('- Statut : ' . $user->getStatus()->name);
        $output->writeln('- Date de création : ' . $user->getDateCreation()?->format('Y-m-d'));
        $output->writeln('- Dernière visite : ' . $user->getLastVisit()?->format('Y-m-d H:i:s'));
        $output->writeln('');
        $output->writeln('Derniers logs de connexion :');
        $logs = $this->llRepository->findBy(['login' => $user->getUsername()], ['date' => 'DESC'], 5);
        foreach ($logs as $log) {
            $output->writeln(sprintf(
                '  [%s] IP: %s | %s',
                $log->getDate()?->format('Y-m-d H:i:s'),
                $log->getIpPublic(),
                $log->isSuccess() ? 'Succès' : 'Échec'
            ));
        }
        if (empty($logs)) {
            $output->writeln('  Aucun log trouvé.');
        }

        return Command::SUCCESS;
    }
}
