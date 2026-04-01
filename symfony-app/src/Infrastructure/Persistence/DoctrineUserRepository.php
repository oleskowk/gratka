<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Model\User;
use App\Domain\Port\UserReadRepositoryInterface;
use App\Domain\Port\UserSaveRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineUserRepository implements UserReadRepositoryInterface, UserSaveRepositoryInterface
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

    public function save(User $user): void
    {
        $this->entityManager->persist($user);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
