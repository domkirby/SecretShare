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
//3.3.0: Removed character bias by rejecting bytes outside the range.
async function generateSecurePassword(length = 16) {
    if (length <= 0) {
        throw new Error("Password length must be greater than 0");
    }

    const charset = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()-_=+[]{}|;:<>,.?/";
    const charsetLength = charset.length;

    const passwordArray = [];
    while (passwordArray.length < length) {
        const randomValues = new Uint8Array(1);
        crypto.getRandomValues(randomValues);

        const randomValue = randomValues[0];
        if (randomValue < Math.floor(256 / charsetLength) * charsetLength) {
            const index = randomValue % charsetLength;
            passwordArray.push(charset[index]);
        }
    }

    return passwordArray.join("");
}


// Function to generate a Diceware passphrase with random separators and a random 3-digit number 
// 3.4.0: Adds backward compatible options.
async function generateDicewarePassphrase(numWords = 6, options = { addSeparators: true, addNumber: true }) {
    if (numWords <= 0) {
        throw new Error("The number of words must be greater than 0.");
    }

    // Fetch the Diceware wordlist
    const fetchWordlist = async () => {
        const response = await fetch('/diceware.json');
        if (!response.ok) {
            throw new Error(`Failed to fetch Diceware list: ${response.statusText}`);
        }
        return response.json(); // Assume this is an array of words
    };

    const wordlist = await fetchWordlist();
    const wordlistLength = wordlist.length;

    if (wordlistLength === 0) {
        throw new Error("Diceware wordlist is empty.");
    }

    // Define the set of separator characters
    const separators = "!@#$%^&*()-_=+[]{}|;:<>,.?/";
    const separatorLength = separators.length;

    // Generate random indices for words and separator characters
    const randomWordIndices = new Uint32Array(numWords);
    const randomSeparatorIndices = new Uint32Array(numWords - 1); // One less separator than words
    crypto.getRandomValues(randomWordIndices);
    crypto.getRandomValues(randomSeparatorIndices);

    // Select words
    const passphraseWords = Array.from(randomWordIndices, (value) => {
        const index = value % wordlistLength;
        return wordlist[index];
    });

    // Randomly capitalize at least one word
    const capitalizeRandomWord = () => {
        const randomIndex = Math.floor(Math.random() * numWords);
        passphraseWords[randomIndex] = passphraseWords[randomIndex][0].toUpperCase() + passphraseWords[randomIndex].slice(1);
    };
    capitalizeRandomWord();

    // Assemble the passphrase
    let passphrase;
    if (options.addSeparators) {
        // Select separators
        const separatorsArray = Array.from(randomSeparatorIndices, (value) => {
            const index = value % separatorLength;
            return separators[index];
        });

        passphrase = passphraseWords[0];
        for (let i = 0; i < separatorsArray.length; i++) {
            passphrase += separatorsArray[i] + passphraseWords[i + 1];
        }
    } else {
        // Use spaces as separators
        passphrase = passphraseWords.join(" ");
    }

    // Add a random 3-digit number at the end if required
    if (options.addNumber) {
        const randomThreeDigitNumber = Math.floor(100 + Math.random() * 900); // Range: 100–999
        passphrase += randomThreeDigitNumber;
    }

    return passphrase;
}
