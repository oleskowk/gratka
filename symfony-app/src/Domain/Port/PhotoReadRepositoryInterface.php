<?php

declare(strict_types=1);

namespace App\Domain\Port;

use App\Entity\Photo;

interface PhotoReadRepositoryInterface
{
    /**
     * @return Photo[]
     */
    public function findAllWithUsers(): array;
}
