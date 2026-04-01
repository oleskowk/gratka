<?php

declare(strict_types=1);

namespace App\Application\Command;

use Psr\Log\LoggerInterface;
use App\Domain\Port\LikeRepositoryInterface;
use App\Domain\Port\PhotoFindRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ToggleLikeCommandHandler
{
    public function __construct(
        private PhotoFindRepositoryInterface $photoRepository,
        private LikeRepositoryInterface $likeRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(ToggleLikeCommand $command): void
    {
        $this->logger->info('Toggling like for photo', [
            'photoId' => $command->photoId,
            'userId' => $command->userId,
        ]);

        $photo = $this->photoRepository->findById($command->photoId);

        if ($photo === null) {
            $this->logger->warning('Photo not found during toggle like', ['photoId' => $command->photoId]);
            throw new NotFoundHttpException('Photo not found');
        }

        if ($this->likeRepository->hasUserLikedPhoto($command->userId, $command->photoId)) {
            $this->logger->debug('User already liked photo, unliking', [
                'photoId' => $command->photoId,
                'userId' => $command->userId,
            ]);
            $this->likeRepository->unlike($command->userId, $command->photoId);
        } else {
            $this->logger->debug('User hasn\'t liked photo, liking', [
                'photoId' => $command->photoId,
                'userId' => $command->userId,
            ]);
            $this->likeRepository->like($command->userId, $command->photoId);
        }
    }
}

