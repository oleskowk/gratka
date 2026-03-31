<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Port\LikeReadRepositoryInterface;
use App\Likes\Like;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineLikeReadRepository implements LikeReadRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function hasUserLikedPhoto(int $userId, int $photoId): bool
    {
        $count = $this->entityManager->createQueryBuilder()
            ->select('COUNT(l.id)')
            ->from(Like::class, 'l')
            ->where('l.user = :userId')
            ->andWhere('l.photo = :photoId')
            ->setParameter('userId', $userId)
            ->setParameter('photoId', $photoId)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count > 0;
    }
}
