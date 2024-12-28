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

    public static function prepareStorageArray(string $data, string $salt, int $iterations)
    {
        $salt = bin2hex($salt);
        $storageArray = [
            'sd' => $data,
            'ss' => $salt,
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
        $parsedArray['ss'] = hex2bin($parsedArray['ss']);
        return $parsedArray;
    }
}

?>
