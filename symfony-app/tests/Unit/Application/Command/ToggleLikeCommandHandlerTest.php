<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Command;

use App\Application\Command\ToggleLikeCommand;
use App\Application\Command\ToggleLikeCommandHandler;
use App\Domain\Model\Photo;
use App\Domain\Port\LikeRepositoryInterface;
use App\Domain\Port\PhotoFindRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ToggleLikeCommandHandlerTest extends TestCase
{
    private PhotoFindRepositoryInterface $photoRepository;
    private LikeRepositoryInterface $likeRepository;
    private ToggleLikeCommandHandler $handler;

    protected function setUp(): void
    {
        $this->photoRepository = $this->createMock(PhotoFindRepositoryInterface::class);
        $this->likeRepository = $this->createMock(LikeRepositoryInterface::class);
        $this->handler = new ToggleLikeCommandHandler(
            $this->photoRepository,
            $this->likeRepository,
            new NullLogger()
        );
    }

    public function testItThrowsExceptionIfPhotoNotFound(): void
    {
        $this->photoRepository->method('findById')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Photo not found');

        ($this->handler)(new ToggleLikeCommand(userId: 1, photoId: 999));
    }

    public function testItUnlikesIfAlreadyLiked(): void
    {
        $photo = $this->createMock(Photo::class);
        $this->photoRepository->method('findById')->willReturn($photo);
        $this->likeRepository->method('hasUserLikedPhoto')->willReturn(true);

        $this->likeRepository->expects($this->once())
            ->method('unlike')
            ->with(1, 100);

        $this->likeRepository->expects($this->never())
            ->method('like');

        ($this->handler)(new ToggleLikeCommand(userId: 1, photoId: 100));
    }

    public function testItLikesIfNotLikedYet(): void
    {
        $photo = $this->createMock(Photo::class);
        $this->photoRepository->method('findById')->willReturn($photo);
        $this->likeRepository->method('hasUserLikedPhoto')->willReturn(false);

        $this->likeRepository->expects($this->once())
            ->method('like')
            ->with(1, 100);

        $this->likeRepository->expects($this->never())
            ->method('unlike');

        ($this->handler)(new ToggleLikeCommand(userId: 1, photoId: 100));
    }
}
