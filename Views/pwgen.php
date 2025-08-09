<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo UI_TITLE; ?> - Password Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/css/main.css?v=<?php echo CURRENT_VERSION; ?>">
</head>
<body>

    <div class="container d-flex align-items-center main-content-container">
        <div class="container-sm inner-content-container" id="aboutContainer">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="mb-0"><?php echo UI_TITLE; ?></h1>
                <div class="d-flex align-items-center gap-2">
                    <label for="themeSelect" class="form-label mb-0 me-2">Theme</label>
                    <select id="themeSelect" class="form-select form-select-sm" style="width:auto">
                        <option value="system">System</option>
                        <option value="light">Light</option>
                        <option value="dark">Dark</option>
                    </select>
                </div>
            </div>
            <hr>
            <h2>Password Generator</h2>
            <p>You can use this tool to generate a password or passphrase to use for your online accounts. This tool generates password <strong>in your browser</strong>, so our server will never see your generated passwords.</p>
            <div class="alert alert-danger" id="errors" style="display: none;"></div>
            <div class="alert alert-primary">The password generator will <strong>not</strong> store anything or create a sharing link. To securely share a secret, <a href="/">create a new secret link</a>.</div>
            <div class="row">
                <div class="col">
                    <h3>Generate a Password</h3>
                    <p>Generate a random password.</p>
                    <div class="mb-3">
                        <label for="passwordLength">Password Length</label>
                        <input type="number" class="form-control" id="passwordLength" value="16" min="8" max="128">
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="generatedPassword" readonly>
                        <button class="btn btn-primary" id="copyPassword" data-bs-toggle="tooltip" data-bs-title="Copy"><i class="bi bi-clipboard"></i></button>
                       
                    </div>
                    <div class="mb-3">
                        <div class="form-text" id="entropy"><strong>Entropy: </strong> <span id="entropyValue">0 bits</span></div>
                        <!-- Compact entropy meter -->
                        <div class="progress mt-2"><div id="entropyBar" class="progress-bar" style="width:0%" role="progressbar" aria-hidden="true"></div></div>
                        <button class="btn btn-primary mt-2" id="generatePassword">Generate Password</button>
                    </div>
                    
                </div>
                <div class="col">
                    <h3>Generate a Diceware Passphrase</h3>
                    <p>Generate a passphrase using the Diceware method.</p>
                    <div class="mb-3">
                        <label for="dicewareLength">Number of Words</label>
                        <input type="number" class="form-control" id="dicewareWordCount" value="6" min="4" max="12">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="dicewareAddDelimiters">
                        <label class="form-check-label" for="dicewareAddDelimiters" data-bs-title="Adds random separator characters between words." data-bs-toggle="tooltip">Add a delimiter between words</label>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="dicewareAddNumber">
                        <label class="form-check-label" for="dicewareAddNumber" data-bs-title="Adds a random 3 digits to the end, helpful for meeting arbitrary password requirements." data-bs-toggle="tooltip">Add a random number at the end</label>
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="generatedDiceware" readonly>
                        <button class="btn btn-primary" id="copyDiceware" data-bs-toggle="tooltip" data-bs-title="Copy"><i class="bi bi-clipboard"></i></button>
                    </div>
                    <div class="mb-3">
                        <div class="form-text" id="dicewareEntropy"><strong>Entropy: </strong> <span id="dicewareEntropyValue">0 bits</span></div>
                        <!-- Compact entropy meter for Diceware -->
                        <div class="progress mt-2"><div id="dicewareEntropyBar" class="progress-bar" style="width:0%" role="progressbar" aria-hidden="true"></div></div>
                        <button class="btn btn-primary mt-2" id="generateDiceware">Generate Diceware Passphrase</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="/js/encryption.js?v=<?php echo CURRENT_VERSION; ?>"></script>
    <!-- Shared UI helpers (toasts, tooltips) -->
    <script src="/js/ui.js?v=<?php echo CURRENT_VERSION; ?>"></script>
    <script src="/js/pwgen.js?v=<?php echo CURRENT_VERSION; ?>"></script>
</body>
</html>