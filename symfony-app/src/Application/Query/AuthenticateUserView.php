<?php

declare(strict_types=1);

namespace App\Application\Query;

final class AuthenticateUserView
{
    public function __construct(
        public readonly int $id,
        public readonly string $username,
    ) {
    }
}
