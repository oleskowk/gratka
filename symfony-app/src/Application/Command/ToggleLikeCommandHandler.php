<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Domain\Port\LikeRepositoryInterface;
use App\Domain\Port\PhotoFindRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ToggleLikeCommandHandler
{
    public function __construct(
        private PhotoFindRepositoryInterface $photoRepository,
        private LikeRepositoryInterface $likeRepository,
    ) {
    }

    public function __invoke(ToggleLikeCommand $command): void
    {
        $photo = $this->photoRepository->findById($command->photoId);

        if ($photo === null) {
            throw new NotFoundHttpException('Photo not found');
        }

        if ($this->likeRepository->hasUserLikedPhoto($command->userId, $command->photoId)) {
            $this->likeRepository->unlike($command->userId, $command->photoId);
        } else {
            $this->likeRepository->like($command->userId, $command->photoId);
        }
    }
}
