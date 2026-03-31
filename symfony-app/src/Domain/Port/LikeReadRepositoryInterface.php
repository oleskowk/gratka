<?php

declare(strict_types=1);

namespace App\Domain\Port;

interface LikeReadRepositoryInterface
{
    public function hasUserLikedPhoto(int $userId, int $photoId): bool;
}
