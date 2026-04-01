<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Command;

use App\Application\Command\ImportPhotosFromPhoenixCommand;
use App\Application\Command\ImportPhotosFromPhoenixCommandHandler;
use App\Application\Exception\UserNotFoundException;
use App\Domain\Exception\PhoenixTokenMissingException;
use App\Domain\Model\Photo;
use App\Domain\Model\User;
use App\Domain\Port\PhoenixApiClientInterface;
use App\Domain\Port\PhotoFindRepositoryInterface;
use App\Domain\Port\PhotoSaveRepositoryInterface;
use App\Domain\Port\UserReadRepositoryInterface;
use App\Infrastructure\ExternalApi\Dto\PhoenixPhotoDto;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class ImportPhotosFromPhoenixCommandHandlerTest extends TestCase
{
    private UserReadRepositoryInterface $userRepository;
    private PhoenixApiClientInterface $phoenixApiClient;
    private PhotoFindRepositoryInterface $photoFindRepository;
    private PhotoSaveRepositoryInterface $photoSaveRepository;
    private LoggerInterface $logger;
    private ImportPhotosFromPhoenixCommandHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserReadRepositoryInterface::class);
        $this->phoenixApiClient = $this->createMock(PhoenixApiClientInterface::class);
        $this->photoFindRepository = $this->createMock(PhotoFindRepositoryInterface::class);
        $this->photoSaveRepository = $this->createMock(PhotoSaveRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new ImportPhotosFromPhoenixCommandHandler(
            $this->phoenixApiClient,
            $this->userRepository,
            $this->photoFindRepository,
            $this->photoSaveRepository,
            $this->logger
        );
    }

    public function testInvokeWithMissingUser(): void
    {
        $this->userRepository->method('findById')->willReturn(null);

        $this->expectException(UserNotFoundException::class);

        ($this->handler)(new ImportPhotosFromPhoenixCommand(1));
    }

    public function testInvokeWithMissingToken(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getPhoenixApiToken')->willReturn(null);
        $this->userRepository->method('findById')->willReturn($user);

        $this->expectException(PhoenixTokenMissingException::class);

        ($this->handler)(new ImportPhotosFromPhoenixCommand(1));
    }

    public function testInvokeSuccessfulImport(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getPhoenixApiToken')->willReturn('valid-token');
        $this->userRepository->method('findById')->willReturn($user);

        $dtos = [
            new PhoenixPhotoDto('ext-1', 'url1'),
            new PhoenixPhotoDto('ext-2', 'url2'),
        ];

        $this->phoenixApiClient->method('fetchPhotos')->willReturn($dtos);

        $this->photoFindRepository->method('findByExternalId')
            ->willReturnMap([
                ['ext-1', 'phoenix', $this->createMock(Photo::class)],
                ['ext-2', 'phoenix', null],
            ]);

        $this->photoSaveRepository->expects($this->once())->method('save')->with($this->isInstanceOf(Photo::class));
        $this->photoSaveRepository->expects($this->once())->method('flush');

        ($this->handler)(new ImportPhotosFromPhoenixCommand(1));
    }
}
