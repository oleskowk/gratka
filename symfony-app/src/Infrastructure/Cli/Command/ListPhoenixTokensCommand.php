<?php

declare(strict_types=1);

namespace App\Infrastructure\Cli\Command;

use App\Domain\Model\User;
use App\Infrastructure\Security\EncryptionServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:list-phoenix-tokens',
    description: 'Lists all users and their Phoenix API tokens (decrypted for review).',
)]
final class ListPhoenixTokensCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EncryptionServiceInterface $encryptionService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Phoenix API Tokens Audit');

        /** @var User[] $users */
        $users = $this->entityManager->getRepository(User::class)->findAll();

        $table = new Table($output);
        $table->setHeaders(['ID', 'Username', 'Status', 'Decrypted Token', 'Stored (Encrypted) Value']);

        foreach ($users as $user) {
            $storedValue = $user->getPhoenixApiToken();
            if (null === $storedValue || '' === $storedValue) {
                $table->addRow([$user->getId(), $user->getUsername(), '<fg=gray>empty</>', '-', '-']);

                continue;
            }

            try {
                $decrypted = $this->encryptionService->decrypt($storedValue);
                $status = '<fg=green>ENCRYPTED</>';
                $displayStored = substr($storedValue, 0, 15).'...';
            } catch (\Exception) {
                $decrypted = $storedValue;
                $status = '<fg=yellow>PLAIN TEXT</>';
                $displayStored = $storedValue;
            }

            $table->addRow([
                $user->getId(),
                $user->getUsername(),
                $status,
                $decrypted,
                $displayStored,
            ]);
        }

        $table->render();

        return Command::SUCCESS;
    }
}
