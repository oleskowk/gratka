<?php

declare(strict_types=1);

namespace App\Application\Query;

use App\Domain\Port\UserReadRepositoryInterface;

final class GetProfileQueryHandler
{
    public function __construct(
        private UserReadRepositoryInterface $userRepository,
    ) {
    }

    public function __invoke(GetProfileQuery $query): ?ProfileView
    {
        $user = $this->userRepository->findById($query->userId);

        if ($user === null) {
            return null;
        }

        return new ProfileView(user: $user);
    }
}
