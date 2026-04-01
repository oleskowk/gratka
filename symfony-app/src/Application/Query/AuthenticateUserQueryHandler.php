<?php

declare(strict_types=1);

namespace App\Application\Query;

use App\Domain\Port\AuthTokenReadRepositoryInterface;
use App\Domain\Port\UserReadRepositoryInterface;
use App\Application\Exception\InvalidTokenException;
use App\Application\Exception\UserNotFoundException;

final class AuthenticateUserQueryHandler
{
    public function __construct(
        private AuthTokenReadRepositoryInterface $authTokenRepository,
        private UserReadRepositoryInterface $userRepository,
    ) {
    }

    public function __invoke(AuthenticateUserQuery $query): AuthenticateUserView
    {
        $token = $this->authTokenRepository->findByToken($query->token);

        if (!$token) {
            throw new InvalidTokenException();
        }

        $user = $this->userRepository->findByUsername($query->username);

        if (!$user) {
            throw new UserNotFoundException();
        }

        return new AuthenticateUserView($user->getId(), $user->getUsername());
    }
}
