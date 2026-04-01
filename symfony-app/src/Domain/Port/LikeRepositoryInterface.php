<?php

declare(strict_types=1);

namespace App\Domain\Port;

interface LikeRepositoryInterface
{
    public function like(int $userId, int $photoId): void;

    public function unlike(int $userId, int $photoId): void;

    public function hasUserLikedPhoto(int $userId, int $photoId): bool;

    /**
     * @return int[]
     */
    public function getLikedPhotoIdsForUser(int $userId): array;
}
