<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Model\Photo;
use App\Domain\Port\PhotoFindRepositoryInterface;
use App\Domain\Port\PhotoReadRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class DoctrinePhotoRepository extends ServiceEntityRepository implements PhotoReadRepositoryInterface, PhotoFindRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Photo::class);
    }

    public function findById(int $id): ?Photo
    {
        return $this->find($id);
    }

    /**
     * @return Photo[]
     */
    public function findAllWithUsers(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->addSelect('u')
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
