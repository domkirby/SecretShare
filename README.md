# SecretShare - Password or other secret sharing tool
This tool is designed to encrypt and store secrets, such as a password, for sharing amongst users.

In the best of times, emailing a password or similar piece of sensitive data is not a good idea, but it is **sometimes necessary**. Using a secure, ephemeral link generator helps eliminate the risk of having persistently stored secrets in an inbox or chat message.

This tool will delete the secret after a user chosen expiration date or number of views (whichever happens first).

## Security / Encryption
If you find any problems with the security of this tool, please open an issue.

When configured, this tool will accept a 'secret' from a user. Once the user submits, the secret is **client side encrypted** using the Javascript WebCrypto API. The encrypted data is then sent to the server and the server will then encrypt the data again using a server side key. See CRYPTOGRAPHY.MD for full details on encryption.

## Requirements
This tool was built on PHP 8.4.1. You will also need:

- An up to date version of MariaDB or MySQL Server
- PHP must have mysqli available
- A webserver that supports rewrites, as this tool uses a PHP router. The tool ships with a `.htaccess` file included. If you are using nginx or another webserver, please tweak appropriately for your environment.
- SSL is **required** for this tool to function, unless you are running it in `localhost` as the WebCrypto APIs will not function in an insecure context.

## Setup
Download the latest [release](https://github.com/domkirby/SecretShare/releases) **or** use a ``git clone`` from the ``main`` branch to make updates easier. 

The public files for this tool live in the `public` directory. The web server should only expose the `public` directory!

- Download the latest release and upload it to your server
- Rename or copy `_config.sample.php` to `_config.php`
- Create a database on your MySQL server that will be used by the tool (be sure to note the datbase name, username, and password)
- Fill in the appropriate constants as documented in `_config.php`. As of ``3.0.0``, encryption keys are unique to each secret and are derived from the secret's ID which is dervied from the URL with ``PBKDF2``. However, you should generate a random key for ``SERVER_SIDE_HMAC_SECRET`` as that is used to safely hash the secret ID in the database. Use either ``openssl rand -hex 32`` or the Windows instructions below to generate a sufficiently random key.
- Once everything is ready, navigate to `https://your.host.name/install` and follow the instructions to complete the database setup
- Once the database is configured, set the `INSTALLED` flag to `true` in `_config.php`. This will prevent the installer from being reused.
- You will then need to configure a cron job or scheduled task (see below)

### Generating a key with Windows
If you are using a machine without OpenSSL available, a key can be generated in Windows PowerShell:
```
$key = new-Object byte[] 32
[System.Security.Cryptography.RandomNumberGenerator]::Create().GetBytes($key)
[Convert]::ToHexString($key)
# Example Output: 3866A6F474F0A6C1D4C551836485D1EB63257E3B8C509C720CB824C3B02211B5
```

### Setting up the Cron
A cron job or scheduled task should run every minute to ensure that expired secrets are promptly deleted from the database. You can do this with either an HTTP call, or a CLI call.

**CLI (recommended)**: Use php to call `/path/to/SecretShare/_cron-cli.php`. Your php executable settings will depend on your hosting environment.

**HTTP:**

- Define the `CRON_SECRET` constant as a random, URL safe value
- Call the `https://your.host.name/cron/{CRON_SECRET}` endpoint every minute

## SecretShare Requires Its Own Hostname
This tool must run in its on hostname, but a subdomain is acceptable. For example, using `share.example.com` should work great but using `example.com/share` will *not* work.