<?php

class SecretShareDatabase
{
    private $connection;

    // Constructor to initialize the database connection
    public function __construct()
    {
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        if ($this->connection->connect_error) {
            throw new Exception('Database connection failed: ' . $this->connection->connect_error);
        }
    }

    // Get the active database connection
    public function getConnection(): mysqli
    {
        return $this->connection;
    }

    // Create the 'secrets' table if it does not already exist
    public function createSecretsTable(): void
    {
        $query = "CREATE TABLE IF NOT EXISTS secrets (
            secret_id VARCHAR(255) NOT NULL PRIMARY KEY,
            expiration BIGINT NOT NULL,
            max_views INT NOT NULL,
            current_views INT NOT NULL DEFAULT 0,
            secret_value LONGTEXT NOT NULL,
            UNIQUE(secret_id)
        )";

        if (!$this->connection->query($query)) {
            throw new Exception('Failed to create table: ' . $this->connection->error);
        }
    }

    // Add a new secret to the 'secrets' table
    public function addSecret(string $secretId, int $expiration, int $maxViews, int $currentViews, string $secretValue): void
    {
        $query = "INSERT INTO secrets (secret_id, expiration, max_views, current_views, secret_value) VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->connection->prepare($query);

        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $this->connection->error);
        }

        $stmt->bind_param('siiis', $secretId, $expiration, $maxViews, $currentViews, $secretValue);

        if (!$stmt->execute()) {
            throw new Exception('Failed to execute statement: ' . $stmt->error);
        }

        $stmt->close();
    }

    // Delete a secret from the 'secrets' table by secret_id
    public function deleteSecret(string $secretId): void
    {
        $query = "DELETE FROM secrets WHERE secret_id = ?";

        $stmt = $this->connection->prepare($query);

        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $this->connection->connect_error);
        }

        $stmt->bind_param('s', $secretId);

        if (!$stmt->execute()) {
            throw new Exception('Failed to execute statement: ' . $stmt->error);
        }

        $stmt->close();
    }

    // Fetch a secret by secret_id and manage view count
    public function fetchSecret(string $secretId): array
    {
        $wasDeleted = false;
        $query = "SELECT * FROM secrets WHERE secret_id = ?";

        $stmt = $this->connection->prepare($query);

        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $this->connection->error);
        }

        $stmt->bind_param('s', $secretId);

        if (!$stmt->execute()) {
            throw new Exception('Failed to execute statement: ' . $stmt->error);
        }

        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('Secret not found.');
        }

        $secret = $result->fetch_assoc();

        $stmt->close();

        // Increment current_views
        $secret['current_views']++;

        if ($secret['current_views'] >= $secret['max_views']) {
            $this->deleteSecret($secretId);
            $wasDeleted = true;
        } else {
            $updateQuery = "UPDATE secrets SET current_views = ? WHERE secret_id = ?";

            $updateStmt = $this->connection->prepare($updateQuery);

            if (!$updateStmt) {
                throw new Exception('Failed to prepare update statement: ' . $this->connection->error);
            }

            $updateStmt->bind_param('is', $secret['current_views'], $secretId);

            if (!$updateStmt->execute()) {
                throw new Exception('Failed to execute update statement: ' . $updateStmt->error);
            }

            $updateStmt->close();
        }

        $ret = [
            'secret' => $secret,
            'wasDeleted' => $wasDeleted
        ];

        return $ret;
    }

    // Check if a secret exists by secret_id
    public function secretExists(string $secretId): bool
    {
        $query = "SELECT 1 FROM secrets WHERE secret_id = ? LIMIT 1";

        $stmt = $this->connection->prepare($query);

        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $this->connection->error);
        }

        $stmt->bind_param('s', $secretId);

        if (!$stmt->execute()) {
            throw new Exception('Failed to execute statement: ' . $stmt->error);
        }

        $result = $stmt->get_result();

        $exists = $result->num_rows > 0;

        $stmt->close();

        return $exists;
    }

    // Delete secrets where expiration is less than or equal to the current epoch time
    public function deleteExpiredSecrets(): void
    {
        $currentEpochTime = time();
        $query = "DELETE FROM secrets WHERE expiration <= ?";

        $stmt = $this->connection->prepare($query);

        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $this->connection->error);
        }

        $stmt->bind_param('i', $currentEpochTime);

        if (!$stmt->execute()) {
            throw new Exception('Failed to execute statement: ' . $stmt->error);
        }

        $stmt->close();
    }

    // Destructor to close the SQL connection
    public function __destruct()
    {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}

?>
