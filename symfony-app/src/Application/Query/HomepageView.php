<?php

declare(strict_types=1);

namespace App\Application\Query;

use App\Entity\User;

/**
 * Read-model DTO returned by GetHomepageQueryHandler.
 *
 * Carries Doctrine entities for now to preserve Twig template compatibility.
 * A further step would map them to pure read DTOs.
 */
final class HomepageView
{
    /**
     * @param array  $photos      Photo entities with eagerly loaded users
     * @param User|null $currentUser Currently authenticated user, or null
     * @param array<int, bool> $userLikes  Map of photoId => liked status
     */
    public function __construct(
        public readonly array $photos,
        public readonly ?User $currentUser,
        public readonly array $userLikes,
    ) {
    }
}
