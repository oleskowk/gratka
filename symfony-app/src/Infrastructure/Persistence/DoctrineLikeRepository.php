<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Port\LikeRepositoryInterface;
use App\Domain\Model\Photo;
use App\Domain\Model\User;
use App\Domain\Model\Like;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineLikeRepository implements LikeRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function like(int $userId, int $photoId): void
    {
        $user = $this->entityManager->getReference(User::class, $userId);
        $photo = $this->entityManager->getReference(Photo::class, $photoId);

        $like = new Like();
        $like->setUser($user);
        $like->setPhoto($photo);

        $this->entityManager->persist($like);

        $this->entityManager->createQueryBuilder()
            ->update(Photo::class, 'p')
            ->set('p.likeCounter', 'p.likeCounter + 1')
            ->where('p.id = :photoId')
            ->setParameter('photoId', $photoId)
            ->getQuery()
            ->execute();

        $this->entityManager->flush();
    }

    public function unlike(int $userId, int $photoId): void
    {
        $like = $this->entityManager->createQueryBuilder()
            ->select('l')
            ->from(Like::class, 'l')
            ->where('l.user = :userId')
            ->andWhere('l.photo = :photoId')
            ->setParameter('userId', $userId)
            ->setParameter('photoId', $photoId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($like === null) {
            return;
        }

        $this->entityManager->remove($like);

        $this->entityManager->createQueryBuilder()
            ->update(Photo::class, 'p')
            ->set('p.likeCounter', 'p.likeCounter - 1')
            ->where('p.id = :photoId')
            ->setParameter('photoId', $photoId)
            ->getQuery()
            ->execute();

        $this->entityManager->flush();
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
