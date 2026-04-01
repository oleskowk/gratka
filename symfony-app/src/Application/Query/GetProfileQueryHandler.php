<?php

declare(strict_types=1);

namespace App\Application\Query;

use App\Domain\Port\UserReadRepositoryInterface;
use Psr\Log\LoggerInterface;

final class GetProfileQueryHandler
{
    public function __construct(
        private UserReadRepositoryInterface $userRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(GetProfileQuery $query): ?ProfileView
    {
        $this->logger->debug('Fetching profile data', ['userId' => $query->userId]);

        $user = $this->userRepository->findById($query->userId);

        if (null === $user) {
            $this->logger->warning('Profile user not found', ['userId' => $query->userId]);

            return null;
        }

        return new ProfileView(user: $user);
    }
}
