<?php

class SecretShareCryptography
{
    public static function hashUniqueId(string $id): string
    {
        return base64_encode(hash_hmac('sha256', $id, STORAGE_HASH_SECRET, true));
    }

    public static function encryptData(string $data): string
    {
        $ivLength = openssl_cipher_iv_length('aes-256-gcm');
        $iv = openssl_random_pseudo_bytes($ivLength);
        $tag = '';

        $ciphertext = openssl_encrypt(
            $data,
            'aes-256-gcm',
            SERVER_SIDE_ENCRYPTION_KEY,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($ciphertext === false) {
            throw new Exception('Encryption failed.');
        }

        // Combine IV, tag, and ciphertext with a delimiter, and encode them as ASCII-safe string
        return base64_encode($iv) . '::' . base64_encode($tag) . '::' . base64_encode($ciphertext);
    }

    public static function decryptData(string $encryptedData): string
    {
        $parts = explode('::', $encryptedData);

        if (count($parts) !== 3) {
            throw new Exception('Invalid encrypted data format.');
        }

        $iv = base64_decode($parts[0]);
        $tag = base64_decode($parts[1]);
        $ciphertext = base64_decode($parts[2]);

        $plaintext = openssl_decrypt(
            $ciphertext,
            'aes-256-gcm',
            SERVER_SIDE_ENCRYPTION_KEY,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($plaintext === false) {
            throw new Exception('Decryption failed.');
        }

        return $plaintext;
    }

    public static function generateUniqueId(): string
    {
        $randomBytes = random_bytes(16);
        $uniqid = uniqid('', true);

        return hash('sha256', $uniqid . $randomBytes);
    }
}

?>