<?php

class SecretShareCryptography
{
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
        $randomBytes = random_bytes(16); //Random bytes are used to ensure the uniqueness and sufficient randomness (128 bits) of the ID
        $uniqid = uniqid('', true); //Uniqid is used to ensure we do not have collisions
        $hash = hash('sha256', $uniqid . $randomBytes, true); //hash these together
        return self::base64UrlEncode($hash); //base64 encode the hash (URL Safe)
    }

    public static function generateHmac(string $data): string
    {
        $hmac = hash_hmac('sha256', $data, SERVER_SIDE_HMAC_SECRET, true);
        return self::base64UrlEncode($hmac);
    }

    public static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function base64UrlDecode(string $data): string
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}

?>