<?php

declare(strict_types=1);

namespace App\Domain\Port;

interface PhoenixApiClientInterface
{
    /**
     * @return \App\Infrastructure\ExternalApi\Dto\PhoenixPhotoDto[]
     */
    public function fetchPhotos(string $apiToken): array;
}
