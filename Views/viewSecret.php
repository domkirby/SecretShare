<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo UI_TITLE; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/css/main.css?v=<?php echo CURRENT_VERSION; ?>">
</head>
<body>

    <div class="container d-flex align-items-center main-content-container">
        <div class="container-sm inner-content-container" id="retrieveSecretContainer">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="mb-0"><?php echo UI_TITLE; ?></h1>
                <div class="btn-group" role="group" aria-label="Theme">
                    <button type="button" class="btn btn-outline-primary" data-theme-choice="light" data-bs-toggle="tooltip" data-bs-title="Light"><i class="bi bi-sun"></i></button>
                    <button type="button" class="btn btn-outline-primary" data-theme-choice="dark" data-bs-toggle="tooltip" data-bs-title="Dark"><i class="bi bi-moon"></i></button>
                    <button type="button" class="btn btn-outline-primary" data-theme-choice="system" data-bs-toggle="tooltip" data-bs-title="System"><i class="bi bi-circle-half"></i></button>
                </div>
            </div>
            <p><?php echo UI_RETRIEVE_EXPLAINER; ?></p>
            <div class="alert alert-danger" id="errors" style="display: none;" role="alert" aria-live="polite">

            </div>
            <form action="#" id="retrieveSecretForm">
                <div class="mb-3" id="passwordDiv" style="display: none;">
                    <label for="password" class="form-label">Password:</label>
                    <input type="password" id="password" class="form-control" required disabled>
                    <div class="form-text">This password was provided when the secret was created. If you do not have the password, you will not be able to retrieve the secret.</div>
                </div>
                <?php if(CLOUDFLARE_TURNSTILE_ENABLED) { ?>
                    <div class="mb-3">
                    <div
                        class="cf-turnstile"
                        data-sitekey="<?php echo CLOUDFLARE_TURNSTILE_SITE_KEY; ?>"
                        data-callback="turnstileCallback"
                        ></div>
                    </div>
                <?php } ?>
                <div class="mb-3">
                    <input type="hidden" name="secretId" id="secretId" value="<?php echo $secretId; ?>">
                    <input type="hidden" name="csrfToken" id="csrfToken" value="<?php echo $this->CSRF_TOKEN; ?>">
                    <input type="hidden" name="cf-turnstile-response" id="cfTurnstileResponse" value="">
                    <button type="submit" class="btn btn-primary btn-lg" id="submitButton" <?php if(CLOUDFLARE_TURNSTILE_ENABLED) echo 'disabled'; ?>>Retrieve Secret</button>
                    <div class="spinner-border" role="status" id="loading" style="<?php if(!CLOUDFLARE_TURNSTILE_ENABLED) echo 'display: none;'; ?>">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </form>
        </div>
        <div class="container-sm inner-content-container" id="decryptedSecretContainer" style="display: none;">
            <h1>Secret Retrieved!</h1>
            <p>Your secret has been retreived. Please be sure to copy it and store it securely, as it will <strong>not</strong> be permanently stored here.</p>
            <div class="alert alert-danger" id="errorsTwo" style="display: none;" role="alert" aria-live="polite">

            </div>
            <div class="alert alert-success" id="secretDeleted" style="display: none;" role="status" aria-live="polite">
                <p>This secret has been deleted</p>
                <a href="/" class="btn btn-primary">Create a new secret</a>
            </div>
            <div class="mb-3 input-group">
                <textarea class="form-control" id="decryptedSecret" rows="3" readonly></textarea>
                <button class="btn btn-primary" id="copySecretButton">Copy Secret</button>
            </div>
            <button class="btn btn-outline-danger" id="deleteSecretBtn">Delete Secret</button>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="/js/encryption.js?v=<?php echo CURRENT_VERSION; ?>"></script>
    <!-- Shared UI helpers (toasts, tooltips) -->
    <script src="/js/ui.js?v=<?php echo CURRENT_VERSION; ?>"></script>
    <script>
    </script>

    <script src="/js/retrieve.js?v=<?php echo CURRENT_VERSION; ?>"></script>
    <?php if(CLOUDFLARE_TURNSTILE_ENABLED) { ?>
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js"></script>
    <?php } ?>
</body>
</html>