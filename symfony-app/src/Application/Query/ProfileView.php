<?php

declare(strict_types=1);

namespace App\Application\Query;

use App\Entity\User;

/**
 * Read-model DTO for the profile page.
 *
 * Carries a Doctrine entity for now to preserve Twig template compatibility.
 */
final class ProfileView
{
    public function __construct(
        public readonly User $user,
    ) {
    }
}
