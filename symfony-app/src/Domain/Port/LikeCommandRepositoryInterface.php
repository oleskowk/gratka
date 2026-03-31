<?php

declare(strict_types=1);

namespace App\Domain\Port;

use App\Domain\Model\Photo;
use App\Domain\Model\User;

interface LikeCommandRepositoryInterface
{
    public function like(User $user, Photo $photo): void;

    public function unlike(User $user, Photo $photo): void;
}
