<?php

declare(strict_types=1);

namespace App\Application\Query;

use App\Domain\Port\LikeRepositoryInterface;
use App\Domain\Port\PhotoReadRepositoryInterface;
use App\Domain\Port\UserReadRepositoryInterface;

final class GetHomepageQueryHandler
{
    public function __construct(
        private PhotoReadRepositoryInterface $photoReadRepository,
        private LikeRepositoryInterface $likeRepository,
        private UserReadRepositoryInterface $userReadRepository,
    ) {
    }

    public function __invoke(GetHomepageQuery $query): HomepageView
    {
        $photos = $this->photoReadRepository->findAllWithUsers();

        $currentUser = null;
        $userLikes = [];

        if ($query->currentUserId !== null) {
            $currentUser = $this->userReadRepository->findById($query->currentUserId);

            if ($currentUser !== null) {
                $likedPhotoIds = $this->likeRepository->getLikedPhotoIdsForUser($query->currentUserId);
                $likedPhotoIdsSet = array_flip($likedPhotoIds);

                foreach ($photos as $photo) {
                    $userLikes[$photo->getId()] = isset($likedPhotoIdsSet[$photo->getId()]);
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
