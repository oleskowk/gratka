<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

interface EncryptionServiceInterface
{
    public function encrypt(string $plainText): string;

    public function decrypt(string $encodedText): string;
}
