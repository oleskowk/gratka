<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Model\AuthToken;
use App\Domain\Port\AuthTokenReadRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineAuthTokenReadRepository implements AuthTokenReadRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function findByToken(string $token): ?AuthToken
    {
        return $this->entityManager->getRepository(AuthToken::class)->findOneBy(['token' => $token]);
    }

    public function findForUser(int $userId): ?AuthToken
    {
        return $this->entityManager->getRepository(AuthToken::class)->findOneBy(['user' => $userId], ['createdAt' => 'DESC']);
    }
}
