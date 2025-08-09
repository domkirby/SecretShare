<?php

class SecretShareParser
{
    //Parses the user's time input (such as "2 days") to a expiration time in epoch
    public static function parseExpirationDate(int $period, string $unit): int
    {
        $secondsInUnit = match ($unit) {
            'days' => 86400,
            'hours' => 3600,
            'minutes' => 60,
            default => throw new InvalidArgumentException('Invalid unit provided. Must be "days", "hours", or "minutes".')
        };

        $maxPeriod = match ($unit) {
            'days' => 5,
            'hours' => 24,
            'minutes' => 60,
        };

        if ($period > $maxPeriod) {
            throw new InvalidArgumentException("Period exceeds maximum allowed value for unit '$unit'.");
        }

        $additionalSeconds = $period * $secondsInUnit;

        return time() + $additionalSeconds;
    }

    // Accept raw bytes, Base64, or Base64URL salt and normalize to raw bytes
    private static function normalizeSaltToBytes(string $salt): string
    {
        // Try standard Base64 (strict)
        if ($salt !== '' && preg_match('/^[A-Za-z0-9+\/]+={0,2}$/', $salt)) {
            $decoded = base64_decode($salt, true);
            if ($decoded !== false) {
                return $decoded;
            }
        }
        // Try Base64URL
        if ($salt !== '' && preg_match('/^[A-Za-z0-9\-_]+$/', $salt)) {
            $b64 = strtr($salt, '-_', '+/');
            $pad = strlen($b64) % 4;
            if ($pad) {
                $b64 .= str_repeat('=', 4 - $pad);
            }
            $decoded = base64_decode($b64, true);
            if ($decoded !== false) {
                return $decoded;
            }
        }
        // Otherwise assume raw bytes already
        return $salt;
    }

    public static function prepareStorageArray(string $data, string $salt, int $iterations)
    {
        // Normalize salt input, then store as hex
        $saltBytes = self::normalizeSaltToBytes($salt);
        $saltHex = bin2hex($saltBytes);

        $storageArray = [
            'sd' => $data,
            'ss' => $saltHex,
            'si' => $iterations
        ];

        return json_encode($storageArray);
    }

    public static function parseStorageArray(string $storageArray): array
    {
        $parsedArray = json_decode($storageArray, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Failed to parse storage array: ' . json_last_error_msg());
        }

        // Validate required fields
        foreach (['sd', 'ss', 'si'] as $k) {
            if (!array_key_exists($k, $parsedArray)) {
                throw new Exception("Missing field '$k' in storage array.");
            }
        }

        // Validate hex-encoded salt and decode
        if (!is_string($parsedArray['ss']) || (strlen($parsedArray['ss']) % 2 !== 0) || !ctype_xdigit($parsedArray['ss'])) {
            throw new Exception('Invalid hex-encoded salt in storage array.');
        }
        $saltBytes = hex2bin($parsedArray['ss']);
        if ($saltBytes === false) {
            throw new Exception('Failed to decode hex-encoded salt.');
        }

        $parsedArray['ss'] = $saltBytes;
        $parsedArray['si'] = (int)$parsedArray['si'];

        return $parsedArray;
    }
}

?>
