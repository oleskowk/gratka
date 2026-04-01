<?php

declare(strict_types=1);

namespace App\Application\Command;

final class ImportPhotosFromPhoenixCommand
{
    public function __construct(
        public readonly int $userId,
    ) {
    }
}
