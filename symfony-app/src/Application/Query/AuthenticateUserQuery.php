<?php

declare(strict_types=1);

namespace App\Application\Query;

final class AuthenticateUserQuery
{
    public function __construct(
        public readonly string $username,
        public readonly string $token,
    ) {
    }
}
