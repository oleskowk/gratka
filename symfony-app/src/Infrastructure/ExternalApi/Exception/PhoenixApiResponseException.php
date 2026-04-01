<?php

declare(strict_types=1);

namespace App\Infrastructure\ExternalApi\Exception;

final class PhoenixApiResponseException extends \RuntimeException
{
    public function __construct(int $statusCode, string $message = 'Unexpected response from Phoenix API')
    {
        parent::__construct(sprintf('%s (Status code: %d)', $message, $statusCode), $statusCode);
    }
}
