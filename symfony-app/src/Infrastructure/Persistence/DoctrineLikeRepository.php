<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Exception\PhotoNotFoundException;
use App\Application\Exception\UserNotFoundException;
use App\Domain\Model\Like;
use App\Domain\Model\Photo;
use App\Domain\Model\User;
use App\Domain\Port\LikeRepositoryInterface;
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

        if (! $user instanceof User) {
            throw new UserNotFoundException();
        }

        if (! $photo instanceof Photo) {
            throw new PhotoNotFoundException();
        }

        $like = new Like();
        $like->setUser($user);
        $like->setPhoto($photo);

        $this->entityManager->persist($like);

        // This is a workaround for a race condition. Better way might be to implement a domain model that would handle this.
        // Still it is possible to like the same photo twice - this should be handled by the domain model and UNIQUE constraint on the database
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

        if (null === $like) {
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

    public function getLikedPhotoIdsForUser(int $userId): array
    {
        $result = $this->entityManager->createQueryBuilder()
            ->select('p.id')
            ->from(Like::class, 'l')
            ->join('l.photo', 'p')
            ->where('l.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();

        return array_column($result, 'id');
    }
}
