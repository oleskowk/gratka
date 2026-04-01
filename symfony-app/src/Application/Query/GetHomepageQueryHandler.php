<?php

declare(strict_types=1);

namespace App\Application\Query;

use App\Domain\Port\LikeRepositoryInterface;
use App\Domain\Port\PhotoReadRepositoryInterface;
use App\Domain\Port\UserReadRepositoryInterface;
use Psr\Log\LoggerInterface;

final class GetHomepageQueryHandler
{
    public function __construct(
        private PhotoReadRepositoryInterface $photoReadRepository,
        private LikeRepositoryInterface $likeRepository,
        private UserReadRepositoryInterface $userReadRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(GetHomepageQuery $query): HomepageView
    {
        $this->logger->debug('Fetching homepage data', ['userId' => $query->currentUserId]);

        $photos = $this->photoReadRepository->findAllWithUsers();

        $this->logger->debug('Found photos', ['count' => count($photos)]);

        $currentUser = null;
        $userLikes = [];

        if (null !== $query->currentUserId) {
            $currentUser = $this->userReadRepository->findById($query->currentUserId);

            if (null !== $currentUser) {
                $this->logger->debug('Current user found', ['userId' => $query->currentUserId]);
                $likedPhotoIds = $this->likeRepository->getLikedPhotoIdsForUser($query->currentUserId);
                $likedPhotoIdsSet = array_flip($likedPhotoIds);

                foreach ($photos as $photo) {
                    $id = $photo->getId();
                    if (null !== $id) {
                        $userLikes[$id] = isset($likedPhotoIdsSet[$id]);
                    }
                }
            }
        }

        return new HomepageView(
            photos: $photos,
            currentUser: $currentUser,
            userLikes: $userLikes,
        );
    }
}
