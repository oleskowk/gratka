<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Application\Exception\UserNotFoundException;
use App\Domain\Port\UserReadRepositoryInterface;
use App\Domain\Port\UserSaveRepositoryInterface;
use Psr\Log\LoggerInterface;

final class UpdatePhoenixTokenCommandHandler
{
    public function __construct(
        private UserReadRepositoryInterface $userRepository,
        private UserSaveRepositoryInterface $userSaveRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(UpdatePhoenixTokenCommand $command): void
    {
        $this->logger->info('Updating Phoenix API token', ['userId' => $command->userId]);

        $user = $this->userRepository->findById($command->userId);
        if (null === $user) {
            $this->logger->error('User not found for token update', ['userId' => $command->userId]);

            throw new UserNotFoundException();
        }

        $user->setPhoenixApiToken($command->token);

        $this->userSaveRepository->save($user);
        $this->userSaveRepository->flush();

        $this->logger->info('Phoenix API token updated successfully', ['userId' => $command->userId]);
    }
}
