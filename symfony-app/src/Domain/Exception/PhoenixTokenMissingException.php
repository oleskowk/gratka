<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class PhoenixTokenMissingException extends \RuntimeException
{
    public function __construct(int $userId)
    {
        parent::__construct(sprintf('No Phoenix API token saved for user ID %d.', $userId), 400);
    }
}
