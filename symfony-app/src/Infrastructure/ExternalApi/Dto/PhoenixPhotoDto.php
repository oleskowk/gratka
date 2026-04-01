<?php

declare(strict_types=1);

namespace App\Infrastructure\ExternalApi\Dto;

final class PhoenixPhotoDto
{
    public function __construct(
        public readonly string $externalId,
        public readonly string $url,
    ) {
    }
}
