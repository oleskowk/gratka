<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Query;

use Psr\Log\NullLogger;
use App\Application\Exception\InvalidTokenException;
use App\Application\Exception\UserNotFoundException;
use App\Application\Query\AuthenticateUserQuery;
use App\Application\Query\AuthenticateUserQueryHandler;
use App\Application\Query\AuthenticateUserView;
use App\Domain\Model\AuthToken;
use App\Domain\Model\User;
use App\Domain\Port\AuthTokenReadRepositoryInterface;
use App\Domain\Port\UserReadRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class AuthenticateUserQueryHandlerTest extends TestCase
{
    private AuthTokenReadRepositoryInterface $authTokenRepository;
    private UserReadRepositoryInterface $userRepository;
    private AuthenticateUserQueryHandler $handler;

    protected function setUp(): void
    {
        $this->authTokenRepository = $this->createMock(AuthTokenReadRepositoryInterface::class);
        $this->userRepository = $this->createMock(UserReadRepositoryInterface::class);
        $this->handler = new AuthenticateUserQueryHandler(
            $this->authTokenRepository,
            $this->userRepository,
            new NullLogger()
        );
    }


    public function test_it_throws_exception_if_token_not_found(): void
    {
        $this->authTokenRepository->method('findByToken')->willReturn(null);

        $this->expectException(InvalidTokenException::class);

        ($this->handler)(new AuthenticateUserQuery('username', 'invalid-token'));
    }

    public function test_it_throws_exception_if_user_not_found(): void
    {
        $token = $this->createMock(AuthToken::class);
        $this->authTokenRepository->method('findByToken')->willReturn($token);
        $this->userRepository->method('findByUsername')->willReturn(null);

        $this->expectException(UserNotFoundException::class);

        ($this->handler)(new AuthenticateUserQuery('unknown-user', 'valid-token'));
    }

    public function test_it_returns_view_if_everything_is_correct(): void
    {
        $token = $this->createMock(AuthToken::class);
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);
        $user->method('getUsername')->willReturn('testuser');

        $this->authTokenRepository->method('findByToken')->willReturn($token);
        $this->userRepository->method('findByUsername')->with('testuser')->willReturn($user);

        $view = ($this->handler)(new AuthenticateUserQuery('testuser', 'valid-token'));

        $this->assertInstanceOf(AuthenticateUserView::class, $view);
        $this->assertEquals(1, $view->id);
        $this->assertEquals('testuser', $view->username);
    }
}
