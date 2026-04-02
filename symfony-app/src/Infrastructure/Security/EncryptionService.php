<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

final class EncryptionService implements EncryptionServiceInterface
{
    private string $key;

    public function __construct(string $base64Key)
    {
        $this->key = base64_decode($base64Key);
        if (32 !== strlen($this->key)) {
            throw new \RuntimeException('Encryption key must be exactly 32 bytes (after base64 decode).');
        }
    }

    public function encrypt(string $plainText): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipherText = sodium_crypto_secretbox($plainText, $nonce, $this->key);

        return base64_encode($nonce.$cipherText);
    }

    public function decrypt(string $encodedText): string
    {
        $decoded = base64_decode($encodedText);
        $nonceSize = SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;

        if (strlen($decoded) <= $nonceSize) {
            throw new \RuntimeException('Encrypted text is too short or invalid.');
        }

        $nonce = substr($decoded, 0, $nonceSize);
        $cipherText = substr($decoded, $nonceSize);

        $plainText = sodium_crypto_secretbox_open($cipherText, $nonce, $this->key);
        if (false === $plainText) {
            throw new \RuntimeException('Failed to decrypt text. Key or data might be compromised.');
        }

        return $plainText;
    }
}
