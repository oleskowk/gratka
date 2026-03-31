<?php

declare(strict_types=1);

namespace App\Domain\Port;

use App\Domain\Model\Photo;

interface PhotoFindRepositoryInterface
{
    public function findById(int $id): ?Photo;
}
