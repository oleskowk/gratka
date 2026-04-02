<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Model\Photo;
use App\Domain\Model\PhotoFilters;
use App\Domain\Port\PhotoFindRepositoryInterface;
use App\Domain\Port\PhotoReadRepositoryInterface;
use App\Domain\Port\PhotoSaveRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Photo> */
final class DoctrinePhotoRepository extends ServiceEntityRepository implements PhotoReadRepositoryInterface, PhotoFindRepositoryInterface, PhotoSaveRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Photo::class);
    }

    public function findById(int $id): ?Photo
    {
        $photo = $this->find($id);

        return $photo instanceof Photo ? $photo : null;
    }

    public function findByExternalId(string $externalId, string $source): ?Photo
    {
        return $this->findOneBy(['externalId' => (string) $externalId, 'source' => $source]);
    }

    /**
     * @return Photo[]
     */
    public function findAllWithUsers(PhotoFilters $filters): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->addSelect('u');

        if (null !== $filters->location) {
            $qb->andWhere('LOWER(p.location) LIKE LOWER(:location)')
                ->setParameter('location', '%'.$filters->location.'%');
        }

        if (null !== $filters->camera) {
            $qb->andWhere('LOWER(p.camera) LIKE LOWER(:camera)')
                ->setParameter('camera', '%'.$filters->camera.'%');
        }

        if (null !== $filters->description) {
            $qb->andWhere('LOWER(p.description) LIKE LOWER(:description)')
                ->setParameter('description', '%'.$filters->description.'%');
        }

        if (null !== $filters->takenAt) {
            $date = \DateTimeImmutable::createFromFormat('Y-m-d', $filters->takenAt);
            if (false !== $date) {
                $start = $date->setTime(0, 0, 0);
                $end = $date->setTime(23, 59, 59);
                $qb->andWhere('p.takenAt BETWEEN :start AND :end')
                    ->setParameter('start', $start)
                    ->setParameter('end', $end);
            }
        }

        if (null !== $filters->username) {
            $qb->andWhere('LOWER(u.username) LIKE LOWER(:username)')
                ->setParameter('username', '%'.$filters->username.'%');
        }

        return $qb->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function save(Photo $photo): void
    {
        $this->getEntityManager()->persist($photo);
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
