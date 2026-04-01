<?php

declare(strict_types=1);

namespace App\Infrastructure\ExternalApi\Exception;

final class InvalidPhoenixTokenException extends \RuntimeException
{
    public function __construct(string $message = 'The provided Phoenix API token is invalid or expired.', int $code = 401, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
