<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\ExternalApi\Factory;

use App\Infrastructure\ExternalApi\Dto\PhoenixPhotoDto;
use App\Infrastructure\ExternalApi\Exception\PhoenixApiResponseException;
use App\Infrastructure\ExternalApi\Factory\PhoenixPhotoFactory;
use PHPUnit\Framework\TestCase;

final class PhoenixPhotoFactoryTest extends TestCase
{
    private PhoenixPhotoFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new PhoenixPhotoFactory();
    }

    public function testCreateFromApiArrayWithValidData(): void
    {
        $data = [
            'id' => 123,
            'photo_url' => 'https://example.com/photo.jpg',
        ];

        $dto = $this->factory->createFromApiArray($data);

        $this->assertInstanceOf(PhoenixPhotoDto::class, $dto);
        $this->assertSame('123', $dto->externalId);
        $this->assertSame('https://example.com/photo.jpg', $dto->url);
    }

    public function testCreateFromApiArrayThrowsExceptionWhenIdIsMissing(): void
    {
        $data = [
            'photo_url' => 'https://example.com/photo.jpg',
        ];

        $this->expectException(PhoenixApiResponseException::class);
        $this->expectExceptionMessage('Invalid API response format: missing or invalid photo id or url.');

        $this->factory->createFromApiArray($data);
    }

    public function testCreateCollectionFromApiArrayWithValidData(): void
    {
        $data = [
            'photos' => [
                ['id' => 1, 'photo_url' => 'url1'],
                ['id' => 2, 'photo_url' => 'url2'],
            ],
        ];

        $collection = $this->factory->createCollectionFromApiArray($data);

        $this->assertCount(2, $collection);
        $this->assertSame('1', $collection[0]->externalId);
        $this->assertSame('2', $collection[1]->externalId);
    }

    public function testCreateCollectionFromApiArrayThrowsExceptionWhenPhotosKeyIsMissing(): void
    {
        $data = ['invalid' => 'format'];

        $this->expectException(PhoenixApiResponseException::class);
        $this->expectExceptionMessage('Invalid API response format: missing photos collection.');

        $this->factory->createCollectionFromApiArray($data);
    }
}
