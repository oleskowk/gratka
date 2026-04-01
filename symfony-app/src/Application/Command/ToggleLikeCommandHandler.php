<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Domain\Port\LikeRepositoryInterface;
use App\Domain\Port\PhotoFindRepositoryInterface;
use App\Domain\Port\UserReadRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ToggleLikeCommandHandler
{
    public function __construct(
        private UserReadRepositoryInterface $userRepository,
        private PhotoFindRepositoryInterface $photoRepository,
        private LikeRepositoryInterface $likeRepository,
    ) {
    }

    public function __invoke(ToggleLikeCommand $command): void
    {
        $user = $this->userRepository->findById($command->userId);
        $photo = $this->photoRepository->findById($command->photoId);

        if ($photo === null) {
            throw new NotFoundHttpException('Photo not found');
        }

        if ($this->likeRepository->hasUserLikedPhoto($command->userId, $command->photoId)) {
            $this->likeRepository->unlike($user, $photo);
        } else {
            $this->likeRepository->like($user, $photo);
        }
    }
}
