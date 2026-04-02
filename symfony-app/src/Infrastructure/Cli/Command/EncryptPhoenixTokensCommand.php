<?php

declare(strict_types=1);

namespace App\Infrastructure\Cli\Command;

use App\Domain\Model\User;
use App\Infrastructure\Security\EncryptionServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:encrypt-phoenix-tokens',
    description: 'Encrypts all plain-text Phoenix API tokens in the database.',
)]
final class EncryptPhoenixTokensCommand extends Command
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
        $io->title('Encrypting Phoenix API Tokens');

        /** @var User[] $users */
        $users = $this->entityManager->getRepository(User::class)->findAll();
        $count = 0;
        $skipped = 0;

        foreach ($users as $user) {
            $token = $user->getPhoenixApiToken();
            if (null === $token || '' === $token) {
                continue;
            }

            try {
                $this->encryptionService->decrypt($token);
                ++$skipped;
            } catch (\Exception) { // If it does not decrypt, it's not encrypted
                $encrypted = $this->encryptionService->encrypt($token);
                $user->setPhoenixApiToken($encrypted);
                ++$count;
            }
        }

        if ($count > 0) {
            $this->entityManager->flush();
            $io->success(sprintf('Successfully encrypted %d tokens (%d skipped because already encrypted).', $count, $skipped));
        } else {
            $io->info(sprintf('No unencrypted tokens found (%d already encrypted/empty).', $skipped));
        }

        return Command::SUCCESS;
    }
}
