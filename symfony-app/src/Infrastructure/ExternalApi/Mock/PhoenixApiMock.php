<?php

declare(strict_types=1);

namespace App\Infrastructure\ExternalApi\Mock;

use App\Domain\Port\PhoenixApiClientInterface;
use App\Infrastructure\ExternalApi\Dto\PhoenixPhotoDto;
use App\Infrastructure\ExternalApi\Exception\InvalidPhoenixTokenException;

final class PhoenixApiMock implements PhoenixApiClientInterface
{
    /** @var PhoenixPhotoDto[] */
    private static array $photos = [];
    private static bool $shouldFailWithAuth = false;

    /** @return PhoenixPhotoDto[] */
    public function fetchPhotos(string $token): array
    {
        if (self::$shouldFailWithAuth) {
            throw new InvalidPhoenixTokenException();
        }

        return self::$photos;
    }

    /** @param PhoenixPhotoDto[] $photos */
    public function setPhotos(array $photos): void
    {
        self::$photos = $photos;
    }

    public function setShouldFailWithAuth(bool $shouldFail): void
    {
        self::$shouldFailWithAuth = $shouldFail;
    }
}
