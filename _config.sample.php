<?php
/**
 * SecretShare Configuration File. This file defines the configuration settings required for the tool to function.
 */

/**
 * Database Settings: The configuration options needed to connect to your MySQL/MariaDB DB
 */

 define('DB_HOST', 'localhost'); //The hostname or IP of your MySQL/MariaDB Server
 define('DB_USER', 'YOUR_SQL_USER'); //The username
 define('DB_PASSWORD', 'YOUR_SQL_PASS'); //The Password
 define('DB_NAME', 'YOUR_DB_NAME'); //The name of the database

 /**
  * Encryption Settings.
  * Set unique and RANDOM encryption values. Heed the example commands below.
  */
  define('SERVER_SIDE_ENCRYPTION_KEY', 'YOUR_RANDOM_KEY'); //The key that the server will use to encrypt user secrets. NOTE: This is encrypting the client-side encrypted data for additional protection.
  # To generate this, run "openssl rand -hex 32" in your terminal.

  /**
   * Cron Secret
   * This secret only applies if using the cron via HTTP to /cron/{secret} to delete expired secrets. 
   * This is a security measure to prevent unauthorized access to the cron script and should be a random, URL safe value.
   */
  define('CRON_SECRET', 'YOUR_RANDOM_HTTP_CRON_SECRET');

/**
 * UI SETTINGS
 * These settings influence various parts of the UI, primarily messages.
 */

 define('UI_TITLE', 'SecretShare Secure Sharing Service'); //The title of the pages.
 
 //this value appears on the main page and explains the tool to the user.
 define('UI_HOME_EXPLAINER', 'Welcome to SecretShare! This tool allows you to securely share secrets with others. Simply enter your secret, and share the link with the recipient. They will need the full link to view the secret (including the part after the "#"). The secret is encrypted on the client side, and then encrypted again on the server side for additional protection. The server does not have the ability to view the original secret, only a holder of the link. The secret is deleted once it hits the maximum views or time you specify.');

//This value appears on the view secret page and explains the tool to the user.
 define('UI_RETRIEVE_EXPLAINER', 'You have received a secret from our SecretShare service. To view the secret, click the button below. Once you view the secret, a view will be counted towards the maximum views.');

//Installed: Set this to true once you have completed the /install portion (e.g. created the database tables).
define('INSTALLED', false);