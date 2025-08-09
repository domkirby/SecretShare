# Cryptography Overview

This application provides zero-knowledge encryption: secrets are encrypted in the browser and the encryption keys never leave the client. The server only stores ciphertext (and non-secret parameters like salt and iteration counts). As a result, the host operating this service cannot recover user secrets.

## Client-side (public/js/encryption.js)

Main responsibilities: key generation, client-side encryption/decryption, password/passphrase generation, and PBKDF2-based key derivation for user-supplied passwords.

Key helpers
- ensureWebCrypto(): Ensures a secure context (HTTPS/localhost) and that SubtleCrypto is available.
- hexToBytes256(hex): Validates a 64-hex-character (256-bit) key and converts it to bytes.
- cryptoRandomInt(min, max): CSPRNG integer in [min, max) using rejection sampling.

Functions
- generateRandomKey(): Uses WebCrypto to generate an AES-GCM 256-bit key and exports it as a 64-char hex string.
- encryptData(keyHex, plaintext):
  - Imports the 256-bit hex key as AES-GCM.
  - Creates a 12-byte IV via crypto.getRandomValues.
  - Encrypts UTF-8 plaintext and returns the payload as "ivBase64:ciphertextBase64".
- decryptData(keyHex, encryptedData):
  - Validates format, Base64-decodes IV and ciphertext, and returns the UTF-8 plaintext.
- generatePBKDF2Key(password, saltLength = 16, iterations = 350000, providedSalt = null):
  - Derives a 256-bit key with PBKDF2-HMAC-SHA-256.
  - Returns { key: hex, salt: Base64, iterations, saltLength }.
- generateSecurePassword(length = 16): Uniformly samples characters using CSPRNG with rejection sampling; batches RNG for efficiency.
- generateDicewarePassphrase(numWords = 6, options):
  - Loads diceware.json, selects words and separators using CSPRNG.
  - Capitalizes one word (non-critical; uses Math.random by design).
  - Optionally appends a CSPRNG 3-digit number.

Notes
- Client ciphertext format: "ivBase64:ciphertextBase64" (no auth tag exposed; AES-GCM tag is carried inside the ciphertext structure managed by WebCrypto).
- All key material stays client-side unless explicitly copied by the user.

## Server-side (Backend/SecretShareCryptography.php)

Main responsibilities: server-side AES-256-GCM utilities, PBKDF2 for server-generated keys, salts/IDs, and HMAC utilities. This supports optional server-side operations; it does not compromise the zero-knowledge model because client encryption keys are never transmitted to the server.

Key helpers
- normalizeKey256(key): Accepts a 64-char hex string or a 32-byte binary key; validates 256-bit length.
- base64UrlEncode(data) / base64UrlDecode(data): URL-safe Base64 with correct padding/validation.

Functions
- encryptData(data, key):
  - Validates/normalizes the key to 32-byte binary.
  - Generates a random 12-byte IV and uses AES-256-GCM with a 16-byte tag.
  - Returns "Base64(IV)::Base64(tag)::Base64(ciphertext)".
- decryptData(encryptedData, key):
  - Validates format and strictly Base64-decodes all parts.
  - Enforces IV length = 12 and tag length = 16 before decryption.
  - Returns plaintext on success.
- deriveKey(password, salt, iterations = PBKDF2_ITERATIONS):
  - PBKDF2-HMAC-SHA-256 deriving 32 bytes (256 bits). Server-side iterations may be lower by design because server-side passwords come from secure randomness.
- generateSalt(): 16 random bytes.
- generateUniqueId(): 32 random bytes returned as Base64URL.
- generateHmac(data): HMAC-SHA-256 using SERVER_SIDE_HMAC_SECRET, returned as Base64URL.

Notes
- Server ciphertext format differs from client: "iv::tag::ciphertext" (all Base64). These formats are intentionally not mixed.
- The server never sees the client’s encryption keys, preserving zero-knowledge.
