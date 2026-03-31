<?php

declare(strict_types=1);

namespace App\Application\Query;

final class GetProfileQuery
{
    public function __construct(
        public readonly int $userId,
    ) {
    }
}
