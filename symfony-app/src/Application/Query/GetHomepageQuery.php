<?php

declare(strict_types=1);

namespace App\Application\Query;

final class GetHomepageQuery
{
    public function __construct(
        public readonly ?int $currentUserId = null,
    ) {
    }
}
