<?php
class SecretShareRoutingHandler {

    protected $CSRF_TOKEN;
    private $viewDirectory;

    public function __construct(string $CSRF_TOKEN)
    {
        $this->CSRF_TOKEN = $CSRF_TOKEN;
        $this->viewDirectory = __DIR__ . "/../Views/";
    }

    public function home(): void 
    {
        require $this->viewDirectory . "home.php";
        exit();
    }

    public function about(): void
    {
        require $this->viewDirectory . "about.php";
        exit();
    }

    public function viewSecretPage(string $secretId): void
    {
        require $this->viewDirectory . "viewSecret.php";
        exit();
    }

    public function notFound(): void
    {
        http_response_code(404);
        require $this->viewDirectory . "notFound.php";
        exit();
    }

    //API Backend Functions
    public function saveSecret(): void
    {
        header("Content-Type: application/json");
        try {
            // Validate CSRF token
            $token = $_POST['token'] ?? '';
            if (empty($token) || !isset($_SESSION['token']) || $token !== $_SESSION['token']) {
                http_response_code(403);
                throw new Exception('CSRF violation detected. Please refresh the page and try again.');
            }
    
            // Retrieve POST parameters
            $secret = $_POST['secret'] ?? '';
            $maxViews = intval($_POST['max_views'] ?? 0);
            $expirationPeriod = intval($_POST['expiration_period'] ?? 0);
            $expirationUnit = $_POST['expiration_unit'] ?? '';
    
            // Validate input
            if (empty($secret) || $maxViews <= 0 || $expirationPeriod <= 0 || empty($expirationUnit)) {
                throw new Exception('Invalid input. All fields are required.');
            }
    
            // Validate expiration unit
            if (!in_array($expirationUnit, ['days', 'hours', 'minutes'], true)) {
                throw new Exception('Invalid expiration unit. Must be "days", "hours", or "minutes".');
            }
    
            // Validate expiration period based on unit
            $maxPeriod = match ($expirationUnit) {
                'days' => 5,
                'hours' => 24,
                'minutes' => 60,
            };
    
            if ($expirationPeriod > $maxPeriod) {
                throw new Exception("Invalid expiration period. Maximum allowed for $expirationUnit is $maxPeriod.");
            }
    
            // Encrypt the secret
            $encryptedSecret = SecretShareCryptography::encryptData($secret);
    
            // Generate a unique secret ID
            $secretId = SecretShareCryptography::generateUniqueId();

            $secretDatabaseId = SecretShareCryptography::generateHmac($secretId);
    
            // Parse expiration date
            $expirationTime = SecretShareParser::parseExpirationDate($expirationPeriod, $expirationUnit);
    
            // Format expiration time as MM/DD/YYYY HH:MM:SS in UTC
            $formattedExpirationTime = gmdate('m/d/Y H:i:s', $expirationTime);
    
            // Store the secret in the database
            $database = new SecretShareDatabase();
            $database->addSecret($secretDatabaseId, $expirationTime, $maxViews, 0, $encryptedSecret);
    
            // Respond with success message
            echo json_encode([
                'success' => true,
                'secret_id' => $secretId,
                'expiration_time' => $formattedExpirationTime . " GMT"
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function fetchSecret($secretId): void
    {
        header("Content-Type: application/json");
        try {
            $token = $_POST['token'] ?? '';
            if (empty($token) || !isset($_SESSION['token']) || $token !== $_SESSION['token']) {
                http_response_code(403);
                throw new Exception('CSRF violation detected. Please refresh the page and try again.');
            }
            // Retrieve secret from database
            $database = new SecretShareDatabase();
            $databaseId = SecretShareCryptography::generateHmac($secretId);
            $secret = $database->fetchSecret($databaseId);
    
            // Decrypt secret
            $decryptedSecret = SecretShareCryptography::decryptData($secret['secret_value']);
    
            // Respond with decrypted secret
            echo json_encode([
                'success' => true,
                'secret' => $decryptedSecret
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function deleteSecret($secretId): void
    {
        header("Content-Type: application/json");
        try {
            $token = $_REQUEST['token'] ?? '';
            if (empty($token) || !isset($_SESSION['token']) || $token !== $_SESSION['token']) {
                http_response_code(403);
                throw new Exception('CSRF violation detected. Please refresh the page and try again.');
            }
            // Delete secret from database
            $database = new SecretShareDatabase();
            $database->deleteSecret($secretId);
    
            // Respond with success message
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function deleteExpiredSecretsCron(): void
    {
        // Delete expired secrets from the database
        $database = new SecretShareDatabase();
        $database->deleteExpiredSecrets();
    }

    public function install(): void
    {
        require $this->viewDirectory . "install.php";
        exit();
    }

}

?>