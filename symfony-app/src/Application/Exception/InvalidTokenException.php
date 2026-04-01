<?php

declare(strict_types=1);

namespace App\Application\Exception;

final class InvalidTokenException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Invalid token', 401);
    }
}
