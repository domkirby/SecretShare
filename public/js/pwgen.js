$(document).ready(function() {
    console.log("Document ready");
    //Inititalize tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    //Define buttons
    //For Passwords
    
    const generatePasswordButton = $("#generatePassword");
    const copyPasswordButton = $("#copyPassword");

    //For Passphrases
    const generatePassphraseButton = $("#generateDiceware");
    const copyPassphraseButton = $("#copyDiceware");


    generatePasswordButton.on("click", async function(e) {
        //e.preventDefault();
        try {
            const passwordLength = parseInt($("#passwordLength").val());
            if(passwordLength < 8) {
                throw new Error("Passwords must be at least 8 characters long.");
            }
            if($("#errors").is(":visible")) {
                $("#errors").hide();
            }
            const password = await generateSecurePassword(passwordLength);
            $("#generatedPassword").val(password);
            $("#generatedPassword").trigger("input");
            const entropy = await calculatePasswordEntropy(password);
            $("#entropyValue").text(entropy.toFixed(2) + " bits");
            const $bar = $("#entropyBar");
            if ($bar.length) {
                const pct = Math.min(100, Math.round((entropy / 128) * 100));
                $bar.css("width", pct+"%");
            }
            copyPasswordButton.on("click", function(e) {
                e.preventDefault();
                navigator.clipboard.writeText(password);
                if (window.showToast) showToast("Password copied");
            });
        } catch(error) {
            tripError(error);
        }
    });

    generatePassphraseButton.on("click", async function(e) {
        //e.preventDefault();
        try {
            const dicewareWordCount = parseInt($("#dicewareWordCount").val());
            const dicewareAddNumber = $("#dicewareAddNumber").is(":checked");
            const dicewareAddDelimiters = $("#dicewareAddDelimiters").is(":checked");
            const options = {
                addSeparators: dicewareAddDelimiters,
                addNumber: dicewareAddNumber
            };
            if(dicewareWordCount < 4) {
                throw new Error("Diceware passphrases must be at least 4 words long.");
            }
            if($("#errors").is(":visible")) {
                $("#errors").hide();
            }
            const passphrase = await generateDicewarePassphrase(dicewareWordCount, options);
            $("#generatedDiceware").val(passphrase);
            $("#generatedDiceware").trigger("input");
            const entropy = await calculatePasswordEntropy(passphrase);
            $("#dicewareEntropyValue").text(entropy.toFixed(2) + " bits");
            const $dbar = $("#dicewareEntropyBar");
            if ($dbar.length) {
                const pct = Math.min(100, Math.round((entropy / 128) * 100));
                $dbar.css("width", pct+"%");
            }
            copyPassphraseButton.on("click", function(e) {
                e.preventDefault();
                navigator.clipboard.writeText(passphrase);
                if (window.showToast) showToast("Passphrase copied");
            });
        } catch(error) {
            tripError(error);
        }
    });
});

function tripError(error)
{
    console.error('Error triggered: ' + error);
    $("#errors").text(error);
    $("#errors").show();
}

async function calculatePasswordEntropy(password) {
    if (!password) return 0;

    // Determine the size of the character set used in the password
    let charSetSize = 0;

    if (/[a-z]/.test(password)) charSetSize += 26; // Lowercase letters
    if (/[A-Z]/.test(password)) charSetSize += 26; // Uppercase letters
    if (/[0-9]/.test(password)) charSetSize += 10; // Numbers
    if (/[^a-zA-Z0-9]/.test(password)) charSetSize += 32; // Symbols (~32 common symbols)

    // If no character set detected (unlikely), return 0
    if (charSetSize === 0) return 0;

    // Calculate entropy using the formula: Length × log2(Character Set Size)
    const entropy = password.length * Math.log2(charSetSize);

    return entropy;
}