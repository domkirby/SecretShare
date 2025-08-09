<?php

class SecretShareCryptography
{
    // Normalize and validate a 256-bit key (accepts 64-hex or 32-byte binary)
    private static function normalizeKey256(string $key): string
    {
        if ($key === '') {
            throw new Exception('Encryption key is required.');
        }

        if (ctype_xdigit($key) && strlen($key) === 64) {
            $bin = hex2bin($key);
            if ($bin === false) {
                throw new Exception('Invalid hex key.');
            }
            $key = $bin;
        }

        if (strlen($key) !== 32) {
            $bits = strlen($key) * 8;
            throw new Exception("[Server]: Invalid key length: {$bits} bits. Key must be 256 bits for AES-256-GCM.");
        }

        return $key;
    }

    public static function encryptData(string $data, string $key = ''): string
    {
        $ivLength = openssl_cipher_iv_length('aes-256-gcm');
        $iv = random_bytes($ivLength); 
        $tag = '';

        // Normalize and validate key (hex or binary) -> 32-byte binary
        $key = self::normalizeKey256($key);

        $ciphertext = openssl_encrypt(
            $data,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',       // AAD (none)
            16        // Explicit GCM tag length
        );

        if ($ciphertext === false) {
            throw new Exception('Encryption failed.');
        }

        // Combine IV, tag, and ciphertext with a delimiter, and encode them as ASCII-safe string
        return base64_encode($iv) . '::' . base64_encode($tag) . '::' . base64_encode($ciphertext);
    }

    public static function decryptData(string $encryptedData, string $key = ''): string
    {
        $parts = explode('::', $encryptedData);

        // Normalize and validate key (hex or binary) -> 32-byte binary
        $key = self::normalizeKey256($key);

        if (count($parts) !== 3) {
            throw new Exception('Invalid encrypted data format.');
        }

        // Strict Base64 decode for all parts
        $iv = base64_decode($parts[0], true);
        $tag = base64_decode($parts[1], true);
        $ciphertext = base64_decode($parts[2], true);

        if ($iv === false || $tag === false || $ciphertext === false) {
            throw new Exception('Invalid base64 in encrypted payload.');
        }

        if (strlen($iv) !== 12) {
            throw new Exception("[Server]: Invalid IV length: " . strlen($iv) . " bytes. Expected 12 bytes.");
        }
        if (strlen($tag) !== 16) {
            throw new Exception("[Server]: Invalid GCM tag length: " . strlen($tag) . " bytes. Expected 16 bytes.");
        }

        $plaintext = openssl_decrypt(
            $ciphertext,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
        
        if ($plaintext === false) {
            $error = "[Server]: Decryption failed.\n";
            while($msg = openssl_error_string()) {
                $error .= $msg . "\n";
            }
            throw new Exception($error);
        }

        return $plaintext;
    }

    public static function deriveKey(string $password, string $salt, int $iterations = PBKDF2_ITERATIONS): string
    {
       //return self::customPBKDF2('sha256', $password, $salt, $iterations, 32);
        return hash_pbkdf2('sha256', $password, $salt, $iterations, 32, true);
    }

    public static function generateSalt(): string
    {
        return random_bytes(16);
    }

    public static function generateUniqueId(): string
    {
        $randomBytes = random_bytes(32); // Generate 32 random bytes (256 bits), extremely unlikely to collide and virtually impossible to guess
        return self::base64UrlEncode($randomBytes); //base64 encode the bits in URL-safe format
    }

    public static function generateHmac(string $data): string
    {
        if(!defined('SERVER_SIDE_HMAC_SECRET') || SERVER_SIDE_HMAC_SECRET === '' || SERVER_SIDE_HMAC_SECRET === 'RANDOM_HMAC_SECRET') {
            throw new Exception('[CONFIG ERROR]: Server-side HMAC secret is not defined or has not been randomized. Check _config.php');
        }
        $hmac = hash_hmac('sha256', $data, SERVER_SIDE_HMAC_SECRET, true);
        return self::base64UrlEncode($hmac);
    }

    public static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function base64UrlDecode(string $data): string
    {
        $b64 = strtr($data, '-_', '+/');
        $pad = strlen($b64) % 4;
        if ($pad) {
            $b64 .= str_repeat('=', 4 - $pad);
        }
        $decoded = base64_decode($b64, true);
        if ($decoded === false) {
            throw new Exception('Invalid base64url input.');
        }
        return $decoded;
    }

    //Dealing with some sort of PHP bug required us to build a custom PBKDF2 function. We'll go back to hash_pbkdf2() when it's fixed.
    private static function customPBKDF2(string $algo, string $password, string $salt, int $iterations, int $length = 32): string
    {
        /*$hashLen = strlen(hash($algo, '', true)); // Get native hash length
        $blocks = ceil($length / $hashLen);
    
        $derivedKey = '';
        for ($i = 1; $i <= $blocks; $i++) {
            $block = $salt . pack('N', $i);
            $blockHash = $blockIntermediate = hash_hmac($algo, $block, $password, true);
    
            for ($j = 1; $j < $iterations; $j++) {
                $blockIntermediate = hash_hmac($algo, $blockIntermediate, $password, true);
                $blockHash ^= $blockIntermediate; // XOR each iteration
            }
    
            $derivedKey .= $blockHash;
        }
    
        return substr($derivedKey, 0, $length);*/
        return hash_pbkdf2($algo, $password, $salt, $iterations, $length, true);
    }
}

?>