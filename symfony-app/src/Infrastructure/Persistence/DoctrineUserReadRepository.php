<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Port\UserReadRepositoryInterface;
use App\Domain\Model\User;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineUserReadRepository implements UserReadRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function findById(int $id): ?User
    {
        return $this->entityManager->find(User::class, $id);
    }

    public function findByUsername(string $username): ?User
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
    }
}
