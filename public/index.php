<?php

use Bramus\Router\Router;

if(! file_exists("../_config.php")) {
    die("Configuration file not found. Please copy _config.sample.php to _config.php and configure the settings.");
}

require_once("../_config.php");
require_once("../vendor/autoload.php");
require_once("../Backend/SecretShareParser.php");
require_once("../Backend/SecretShareDatabase.php");
require_once("../Backend/SecretShareCryptography.php");
require_once("../Backend/SecretShareSession.php");
require_once("../Backend/SecretShareRoutingHandler.php");
//Fill for PBKDF2_ITERATIONS if not defined in _config.php
if(!defined('PBKDF2_ITERATIONS')) {
    define('PBKDF2_ITERATIONS', 100000);
}

//Fill for MAXIMUM_VIEWS if not defined in _config.php
if(!defined('MAXIMUM_VIEWS')) {
    define('MAXIMUM_VIEWS', 5);
}

session_start();
$CSRF_TOKEN = SecretShareSession::initiateCsrfToken();

$HANDLER = new SecretShareRoutingHandler($CSRF_TOKEN);

$router = new Router;

$router->get('/', function() {
    global $HANDLER;
    $HANDLER->home();
});

$router->get('/about', function() {
    global $HANDLER;
    $HANDLER->about();
});

$router->get('/secret/{secretId}', function($secretId) {
    global $HANDLER;
    $db = new SecretShareDatabase();
    $secretIdHmac = SecretShareCryptography::generateHmac($secretId);
    if($db->secretExists($secretIdHmac)) {
        $HANDLER->viewSecretPage($secretId);
    } else {
        $HANDLER->notFound();
    }
});

//HTTP Cron
$router->get('/cron/{secret}', function($secret) {
    global $HANDLER;
    if($secret === CRON_SECRET) {
        $HANDLER->deleteExpiredSecretsCron();
    } else {
        http_response_code(404);
        exit();
    }
});

//API Routes
$router->post('/api/saveSecret', function() {
    global $HANDLER;
    $HANDLER->saveSecret();
});

$router->post('/api/retrieveSecret/{secretId}', function($secretId) {
    global $HANDLER;
    $HANDLER->fetchSecret($secretId);
});

$router->post('/api/deleteSecret/{secretId}', function($secretId) {
    global $HANDLER;
    $HANDLER->deleteSecret($secretId);
});

//installer route
$router->get('/install', function() {
    if(! INSTALLED) {
        global $HANDLER;
        $HANDLER->install();
    } else {
        http_response_code(404);
        exit();
    }
    
});

$router->post('/install', function() {
    if(! INSTALLED) {
        try {
            $db = new SecretShareDatabase();
            $db->createSecretsTable();
            unset($db);
            echo "Installation complete. Please set INSTALLED to true in _config.php.";
            exit();
        } catch(Exception $e) {
            header("Location: /install?error=" . $e->getMessage());
        }
    } else {
        http_response_code(404);
        exit();
    }
});

$router->run();