<?php

declare(strict_types=1);

namespace App\Domain\Port;

use App\Entity\Photo;
use App\Entity\User;

interface LikeCommandRepositoryInterface
{
    public function like(User $user, Photo $photo): void;

    public function unlike(User $user, Photo $photo): void;
}
