<?php

declare(strict_types=1);

namespace App\Domain\Port;

use App\Domain\Model\Photo;

interface PhotoReadRepositoryInterface
{
    /**
     * @return Photo[]
     */
    public function findAllWithUsers(): array;
}
