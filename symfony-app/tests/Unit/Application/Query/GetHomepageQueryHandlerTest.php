<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Query;

use App\Application\Query\GetHomepageQuery;
use App\Application\Query\GetHomepageQueryHandler;
use App\Domain\Model\Photo;
use App\Domain\Model\User;
use App\Domain\Port\LikeRepositoryInterface;
use App\Domain\Port\PhotoReadRepositoryInterface;
use App\Domain\Port\UserReadRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class GetHomepageQueryHandlerTest extends TestCase
{
    private PhotoReadRepositoryInterface $photoReadRepository;
    private LikeRepositoryInterface $likeRepository;
    private UserReadRepositoryInterface $userReadRepository;
    private GetHomepageQueryHandler $handler;

    protected function setUp(): void
    {
        $this->photoReadRepository = $this->createMock(PhotoReadRepositoryInterface::class);
        $this->likeRepository = $this->createMock(LikeRepositoryInterface::class);
        $this->userReadRepository = $this->createMock(UserReadRepositoryInterface::class);
        $this->handler = new GetHomepageQueryHandler(
            $this->photoReadRepository,
            $this->likeRepository,
            $this->userReadRepository
        );
    }

    public function test_it_returns_homepage_without_user_likes(): void
    {
        $photos = [$this->createMock(Photo::class)];
        $this->photoReadRepository->method('findAllWithUsers')->willReturn($photos);

        $view = ($this->handler)(new GetHomepageQuery());

        $this->assertSame($photos, $view->photos);
        $this->assertNull($view->currentUser);
        $this->assertEmpty($view->userLikes);
    }

    public function test_it_returns_homepage_with_user_likes_if_logged_in(): void
    {
        $user = $this->createMock(User::class);
        $photo1 = $this->createMock(Photo::class);
        $photo1->method('getId')->willReturn(101);
        $photo2 = $this->createMock(Photo::class);
        $photo2->method('getId')->willReturn(102);

        $photos = [$photo1, $photo2];

        $this->photoReadRepository->method('findAllWithUsers')->willReturn($photos);
        $this->userReadRepository->method('findById')->with(1)->willReturn($user);

        $this->likeRepository->method('hasUserLikedPhoto')
            ->willReturnMap([
                [1, 101, true],
                [1, 102, false],
            ]);

        $view = ($this->handler)(new GetHomepageQuery(currentUserId: 1));

        $this->assertSame($photos, $view->photos);
        $this->assertSame($user, $view->currentUser);
        $this->assertEquals([101 => true, 102 => false], $view->userLikes);
    }

    public function test_it_handles_missing_current_user_id_gracefully(): void
    {
        $photos = [$this->createMock(Photo::class)];
        $this->photoReadRepository->method('findAllWithUsers')->willReturn($photos);
        $this->userReadRepository->method('findById')->willReturn(null);

        $view = ($this->handler)(new GetHomepageQuery(currentUserId: 99));

        $this->assertSame($photos, $view->photos);
        $this->assertNull($view->currentUser);
        $this->assertEmpty($view->userLikes);
    }
}
