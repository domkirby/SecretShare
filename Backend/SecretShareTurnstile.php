<?php

class SecretShareTurnstile {

    private const TURNSTILE_URL = "https://challenges.cloudflare.com/turnstile/v0/siteverify";

    public static function checkTurnstileResponse(string $response) : bool {
        if(!defined('CLOUDFLARE_TURNSTILE_SECRET_KEY') || CLOUDFLARE_TURNSTILE_SECRET_KEY === '') {
            throw new Exception('CLOUDFLARE_TURNSTILE_SECRET_KEY is not defined or is empty.');
        }

        $payload = [
            'secret' => CLOUDFLARE_TURNSTILE_SECRET_KEY,
            'response' => $response
        ];
        $json_payload = json_encode($payload);

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, self::TURNSTILE_URL);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
        curl_close($ch);
        } catch (Exception $e) {
            throw new Exception('Error contacting Cloudflare Turnstile to check CAPTCHA');
        }
        $response = json_decode($response, true);
        
        if($response['success'] == true) {
            return true;
        } else {
            return false;
        }
    }
}