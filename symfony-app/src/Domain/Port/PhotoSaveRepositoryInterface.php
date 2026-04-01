<?php

declare(strict_types=1);

namespace App\Domain\Port;

use App\Domain\Model\Photo;

interface PhotoSaveRepositoryInterface
{
    public function save(Photo $photo): void;

    public function flush(): void;
}
