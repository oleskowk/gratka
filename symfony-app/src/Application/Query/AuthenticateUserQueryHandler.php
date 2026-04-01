<?php

declare(strict_types=1);

namespace App\Application\Query;

use App\Application\Exception\InvalidTokenException;
use App\Application\Exception\UserNotFoundException;
use App\Domain\Port\AuthTokenReadRepositoryInterface;
use App\Domain\Port\UserReadRepositoryInterface;
use Psr\Log\LoggerInterface;

final class AuthenticateUserQueryHandler
{
    public function __construct(
        private AuthTokenReadRepositoryInterface $authTokenRepository,
        private UserReadRepositoryInterface $userRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(AuthenticateUserQuery $query): AuthenticateUserView
    {
        $this->logger->info('Authenticating user', [
            'username' => $query->username,
        ]);

        $token = $this->authTokenRepository->findByToken($query->token);

        if (! $token) {
            $this->logger->warning('Authentication failed: invalid token');

            throw new InvalidTokenException();
        }

        $user = $this->userRepository->findByUsername($query->username);

        if (! $user) {
            $this->logger->warning('Authentication failed: user not found', ['username' => $query->username]);

            throw new UserNotFoundException();
        }

        $this->logger->info('User authenticated successfully', [
            'userId' => $user->getId(),
            'username' => $user->getUsername(),
        ]);

        return new AuthenticateUserView((int) $user->getId(), $user->getUsername());
    }
}
