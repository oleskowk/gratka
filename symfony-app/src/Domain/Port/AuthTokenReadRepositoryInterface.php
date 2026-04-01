<?php

declare(strict_types=1);

namespace App\Domain\Port;

use App\Domain\Model\AuthToken;

interface AuthTokenReadRepositoryInterface
{
    public function findByToken(string $token): ?AuthToken;
}
