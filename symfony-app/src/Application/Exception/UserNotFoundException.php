<?php

declare(strict_types=1);

namespace App\Application\Exception;

final class UserNotFoundException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('User not found', 404);
    }
}
