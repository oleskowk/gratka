<?php

declare(strict_types=1);

namespace App\Application\Command;

final class UpdatePhoenixTokenCommand
{
    public function __construct(
        public readonly int $userId,
        public readonly string $token,
    ) {
    }
}
