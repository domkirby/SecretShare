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

// Example usage
/*
(async () => {
    const key = await generateRandomKey();
    console.log("Generated Key:", key);

    try {
        const plaintext = "Hello, world!";
        const encryptedData = await encryptData(key, plaintext);
        console.log("Encrypted Data:", encryptedData);

        const decryptedData = await decryptData(key, encryptedData);
        console.log("Decrypted Data:", decryptedData);
    } catch (error) {
        console.error("An error occurred during encryption or decryption:", error);
    }
})();
*/