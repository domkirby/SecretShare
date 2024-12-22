<?php

class SecretShareSession {

    public static function initiateCsrfToken(): string
    {
        if(!isset($_SESSION['token'])) {
            $token = base64_encode( random_bytes(16) );

            $_SESSION['token'] = $token;
        } else {
            $token = $_SESSION['token'];
        }

        return $token;
    }

}

?>