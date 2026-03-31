<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Port\PhotoFindRepositoryInterface;
use App\Entity\Photo;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrinePhotoFindRepository implements PhotoFindRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function findById(int $id): ?Photo
    {
        return $this->entityManager->find(Photo::class, $id);
    }
}
