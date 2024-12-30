// Function to generate a random 256-bit key using SubtleCrypto
async function generateRandomKey() {
    const key = await crypto.subtle.generateKey(
        {
            name: "AES-GCM",
            length: 256
        },
        true,
        ["encrypt", "decrypt"]
    );

    const exportedKey = await crypto.subtle.exportKey("raw", key);
    return Array.from(new Uint8Array(exportedKey)).map(byte => byte.toString(16).padStart(2, '0')).join(''); // Hexadecimal string
}

// Function to encrypt a string using AES-GCM
async function encryptData(keyHex, plaintext) {
    try {
        const keyBytes = Uint8Array.from(keyHex.match(/.{1,2}/g).map(byte => parseInt(byte, 16))); // Convert hex to bytes
        const key = await crypto.subtle.importKey(
            "raw",
            keyBytes,
            { name: "AES-GCM" },
            false,
            ["encrypt"]
        );

        const iv = crypto.getRandomValues(new Uint8Array(12)); // 96-bit IV
        const encoder = new TextEncoder();
        const plaintextBytes = encoder.encode(plaintext);

        const ciphertext = await crypto.subtle.encrypt(
            { name: "AES-GCM", iv },
            key,
            plaintextBytes
        );

        // Combine IV and ciphertext as a Base64-safe string
        const ivBase64 = btoa(String.fromCharCode(...iv));
        const ciphertextBase64 = btoa(String.fromCharCode(...new Uint8Array(ciphertext)));

        return `${ivBase64}:${ciphertextBase64}`;
    } catch (error) {
        console.error("Encryption failed:", error);
        throw new Error("Encryption failed. Please check your inputs.");
    }
}

// Function to decrypt a string using AES-GCM
async function decryptData(keyHex, encryptedData) {
    try {
        const keyBytes = Uint8Array.from(keyHex.match(/.{1,2}/g).map(byte => parseInt(byte, 16))); // Convert hex to bytes
        const key = await crypto.subtle.importKey(
            "raw",
            keyBytes,
            { name: "AES-GCM" },
            false,
            ["decrypt"]
        );

        const [ivBase64, ciphertextBase64] = encryptedData.split(":");
        const iv = Uint8Array.from(atob(ivBase64).split("").map(char => char.charCodeAt(0)));
        const ciphertext = Uint8Array.from(atob(ciphertextBase64).split("").map(char => char.charCodeAt(0)));

        const decryptedBytes = await crypto.subtle.decrypt(
            { name: "AES-GCM", iv },
            key,
            ciphertext
        );

        const decoder = new TextDecoder();
        return decoder.decode(decryptedBytes);
    } catch (error) {
        console.error("Decryption failed:", error);
        throw new Error("Decryption failed. Please check your inputs.");
    }
}

async function generatePBKDF2Key(password, saltLength = 16, iterations = 350000, providedSalt = null) {
    // Generate a random salt or use the provided Base64 salt
    const salt = providedSalt 
        ? Uint8Array.from(atob(providedSalt), c => c.charCodeAt(0))
        : crypto.getRandomValues(new Uint8Array(saltLength));

    // Convert the password to a Uint8Array
    const encoder = new TextEncoder();
    const passwordBuffer = encoder.encode(password);

    // Import the password as a key
    const keyMaterial = await crypto.subtle.importKey(
        "raw",
        passwordBuffer,
        { name: "PBKDF2" },
        false,
        ["deriveBits"]
    );

    // Derive the key using PBKDF2
    const derivedBits = await crypto.subtle.deriveBits(
        {
            name: "PBKDF2",
            salt: salt,
            iterations: iterations,
            hash: "SHA-256",
        },
        keyMaterial,
        256
    );

    // Convert the derived bits to a Uint8Array
    const derivedKey = new Uint8Array(derivedBits);

    // Convert the derived key to hex
    const keyHex = Array.from(derivedKey).map(byte => byte.toString(16).padStart(2, '0')).join('');

    // Return the derived key as hex, the salt as Base64, and the number of iterations
    return {
        key: keyHex,
        salt: btoa(String.fromCharCode(...salt)),
        iterations: iterations,
        saltLength: salt.length
    };
}

//Uses the Web Crypto API to generate a secure password from random bytes.
async function generateSecurePassword(length = 16) {
    if (length <= 8) {
        throw new Error("Password length must be greater than 8");
    }

    const charset = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()-_=+[]{}|;:<>,.?/~`";
    const charsetLength = charset.length;

    // Generate an array of random numbers
    const randomValues = new Uint8Array(length);
    crypto.getRandomValues(randomValues);

    // Map random numbers to characters in the charset
    const passwordArray = Array.from(randomValues, (value) => charset[value % charsetLength]);

    // Combine into a single string
    return passwordArray.join("");
}