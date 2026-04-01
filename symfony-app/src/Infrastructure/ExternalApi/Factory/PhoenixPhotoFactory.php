<?php

declare(strict_types=1);

namespace App\Infrastructure\ExternalApi\Factory;

use App\Infrastructure\ExternalApi\Dto\PhoenixPhotoDto;
use App\Infrastructure\ExternalApi\Exception\PhoenixApiResponseException;

final class PhoenixPhotoFactory
{
    /**
     * @param array<mixed, mixed> $data
     */
    public function createFromApiArray(array $data): PhoenixPhotoDto
    {
        $id = $data['id'] ?? null;
        $url = $data['photo_url'] ?? null;

        if (! is_scalar($id) || ! is_scalar($url)) {
            throw new PhoenixApiResponseException(500, 'Invalid API response format: missing or invalid photo id or url.');
        }

        return new PhoenixPhotoDto(
            externalId: (string) $id,
            url: (string) $url,
        );
    }

    /**
     * @param array<mixed, mixed> $data
     *
     * @return PhoenixPhotoDto[]
     */
    public function createCollectionFromApiArray(array $data): array
    {
        $photos = $data['photos'] ?? null;

        if (! is_array($photos)) {
            throw new PhoenixApiResponseException(500, 'Invalid API response format: missing photos collection.');
        }

        $dtos = [];
        foreach ($photos as $photoData) {
            if (! is_array($photoData)) {
                continue;
            }
            $dtos[] = $this->createFromApiArray($photoData);
        }

        return $dtos;
    }
}
