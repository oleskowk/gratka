<?php

declare(strict_types=1);

namespace App\Domain\Port;

use App\Domain\Model\User;

interface UserSaveRepositoryInterface
{
    public function save(User $user): void;

    public function flush(): void;
}
