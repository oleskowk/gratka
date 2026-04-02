<?php

declare(strict_types=1);

namespace App\Domain\Port;

use App\Domain\Model\Photo;
use App\Domain\Model\PhotoFilters;

interface PhotoReadRepositoryInterface
{
    /**
     * @return Photo[]
     */
    public function findAllWithUsers(PhotoFilters $filters): array;
}
