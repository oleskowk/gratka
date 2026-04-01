<?php

declare(strict_types=1);

namespace App\Domain\Port;

use App\Domain\Model\User;

interface UserReadRepositoryInterface
{
    /**
     * Returns a doctrine entity, as it's coupled with Twig template.
     * In the future, it would be better to map it to a DTO.
     */
    public function findById(int $id): ?User;

    public function findByUsername(string $username): ?User;
}
