<?php

declare(strict_types=1);

namespace App\Domain\Port;

use App\Domain\Model\Photo;
use App\Domain\Model\User;

interface LikeRepositoryInterface
{
    public function like(int $userId, int $photoId): void;

    public function unlike(int $userId, int $photoId): void;

    public function hasUserLikedPhoto(int $userId, int $photoId): bool;
}
