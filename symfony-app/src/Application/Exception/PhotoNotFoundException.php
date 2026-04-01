<?php

declare(strict_types=1);

namespace App\Application\Exception;

final class PhotoNotFoundException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Photo not found', 404);
    }
}
