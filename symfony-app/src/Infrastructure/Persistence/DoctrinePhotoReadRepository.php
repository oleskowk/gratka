<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Port\PhotoReadRepositoryInterface;
use App\Entity\Photo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class DoctrinePhotoReadRepository extends ServiceEntityRepository implements PhotoReadRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Photo::class);
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
