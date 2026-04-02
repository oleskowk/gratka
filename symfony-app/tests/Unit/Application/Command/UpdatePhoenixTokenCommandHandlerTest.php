<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Command;

use App\Application\Command\UpdatePhoenixTokenCommand;
use App\Application\Command\UpdatePhoenixTokenCommandHandler;
use App\Application\Exception\UserNotFoundException;
use App\Domain\Model\User;
use App\Domain\Port\UserReadRepositoryInterface;
use App\Domain\Port\UserSaveRepositoryInterface;
use App\Infrastructure\Security\EncryptionServiceInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class UpdatePhoenixTokenCommandHandlerTest extends TestCase
{
    private UserReadRepositoryInterface $userRepository;
    private UserSaveRepositoryInterface $userSaveRepository;
    private EncryptionServiceInterface $encryptionService;
    private LoggerInterface $logger;
    private UpdatePhoenixTokenCommandHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserReadRepositoryInterface::class);
        $this->userSaveRepository = $this->createMock(UserSaveRepositoryInterface::class);
        $this->encryptionService = $this->createMock(EncryptionServiceInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new UpdatePhoenixTokenCommandHandler(
            $this->userRepository,
            $this->userSaveRepository,
            $this->encryptionService,
            $this->logger
        );
    }

    public function testInvokeWithMissingUser(): void
    {
        $this->userRepository->method('findById')->willReturn(null);

        $this->expectException(UserNotFoundException::class);

        ($this->handler)(new UpdatePhoenixTokenCommand(1, 'some-token'));
    }

    public function testInvokeSuccessfulTokenUpdate(): void
    {
        $user = $this->createMock(User::class);
        $this->userRepository->method('findById')->willReturn($user);

        $rawToken = 'my-secret-token';
        $encryptedToken = 'encrypted-string';

        $this->encryptionService->expects($this->once())
            ->method('encrypt')
            ->with($rawToken)
            ->willReturn($encryptedToken);

        $user->expects($this->once())
            ->method('setPhoenixApiToken')
            ->with($encryptedToken);

        $this->userSaveRepository->expects($this->once())->method('save')->with($user);
        $this->userSaveRepository->expects($this->once())->method('flush');

        ($this->handler)(new UpdatePhoenixTokenCommand(1, $rawToken));
    }
}
