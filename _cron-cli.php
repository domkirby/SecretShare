<?php
/**
 * This is to be called via cron to delete expired secrets. It is a CLI script.
 * If you are using a web cron, you should use the /cron/{secret} route instead. You will need to define CRON_SECRET in _config.php.
 */
require("_config.php");
require("Backend/SecretShareDatabase.php");

$database = new SecretShareDatabase();
$database->deleteExpiredSecrets();
exit();