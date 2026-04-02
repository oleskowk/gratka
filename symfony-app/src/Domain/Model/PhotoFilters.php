<?php

declare(strict_types=1);

namespace App\Domain\Model;

final class PhotoFilters
{
    public function __construct(
        public readonly ?string $location = null,
        public readonly ?string $camera = null,
        public readonly ?string $description = null,
        public readonly ?string $takenAt = null,
        public readonly ?string $username = null,
    ) {
    }

    public static function empty(): self
    {
        return new self();
    }
}
