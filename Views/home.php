<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo UI_TITLE; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-dark-5@1.1.3/dist/css/bootstrap-dark.min.css" rel="stylesheet" crossorigin="anonymous" media="(prefers-color-scheme: dark)">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/css/main.css?v=<?php echo CURRENT_VERSION; ?>">
</head>
<body>

    <div class="container d-flex align-items-center main-content-container">
        <div class="container-sm inner-content-container" id="createSecretContainer">
            <h1><?php echo UI_TITLE; ?></h1>
            <p><?php echo UI_HOME_EXPLAINER; ?></p>
            <div class="alert alert-danger" id="errors" style="display: none;">

            </div>
            <?php if(! INSTALLED) { ?>
                <div class="alert alert-danger">
                    <strong>DANGER:</strong> The application is not installed. Please run the <a href="/install">/install</a> route to create the necessary database tables. Once this is complete, set INSTALLED to true in _config.php.
                </div>
            <?php } ?>
            <form action="#" id="secretForm">
                <div class="mb-3">
                    <label for="secret" class="form-label">Your Secret:</label>
                    <textarea id="secret" class="form-control" required></textarea>
                    <input type="hidden" name="secret" id="encryptedSecret">
                </div>
                <div class="mb-3">
                    <label for="maxViews" class="form-label">Max Views:</label>
                    <div class="input-group">
                        <input type="number" id="maxViews" class="form-control" name="max_views" value="1" max="<?php echo MAXIMUM_VIEWS; ?>" required>
                        <span class="input-group-text">Views</span>
                    </div>
                    <div class="form-text">You can set up to <?php echo htmlspecialchars(MAXIMUM_VIEWS); ?> views.</div>
                </div>
                <div class="mb-3">
                    <label for="expiration" class="form-label">Expire After:</label>
                    <div class="input-group">
                        <input type="number" id="expiration" class="form-control" name="expiration_period" value="1" required>
                        <select id="expirationUnit" class="form-select" name="expiration_unit" required>
                            <option value="days" selected>Days</option>
                            <option value="hours">Hours</option>
                            <option value="minutes">Minutes</option>
                        </select>
                    </div>
                    <div class="form-text">Secrets cannot be stored more than 5 days.</div>
                </div>
                <div class="mb-3">
                    <input type="checkbox" name="" id="useCustomPassword" class="form-check-input">
                    <label for="useCustomPassword" class="form-check-label">Use a custom password?</label>
                    <div class="form-text">By default, a random password will be appended to the end of the URL. If you wish to use your own password instead, check this box.</div>
                </div>
                <div class="mb-3" id="customPasswordDiv" style="display: none;">
                    <label for="customPassword" class="form-label">Custom Password:</label>
                    <div class="input-group">
                        <input type="password" id="customPassword" class="form-control" minlength="8" disabled>
                        <button class="btn btn-outline-success" id="togglePassword" alt="Toggle Password Field Visibility" data-bs-toggle="tooltip" data-bs-title="Toggle password visibility"><i class="bi bi-eye-slash" id="togglePasswordInnerContent"></i></button>
                        <button class="btn btn-outline-primary" id="generatePasswordButton" alt="Generate A Password" data-bs-toggle="tooltip" data-bs-title="Generate a password"><i class="bi bi-arrow-clockwise"></i></button>
                    </div>
                    <div class="form-text">This password will be required to view the secret. <strong>Strength:</strong> <span id="passwordStrength">None</span></div>
                    <div class="alert alert-warning form-text"><strong>WARNING:</strong> If you use your own password, the encryption key will be derived from the password. Choose a good password. If you lose this password, it will be impossible to view the contents of the secret.</div>
                </div>
                <div class="mb-3">
                    <input type="hidden" name="token" value="<?php echo $this->CSRF_TOKEN; ?>">
                    <button type="submit" class="btn btn-primary" id="submitButton">Save Secret</button>
                    <div class="spinner-border" role="status" id="loading" style="display: none;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </form>
            <div class="mb-3 text-end">
                <a href="/about" class="btn btn-outline-info btn-sm">About This Tool</a>
            </div>
        </div>
        <div class="container-sm inner-content-container" id="secretLinkContainer" style="display: none;">
            <h1>Secret Created!</h1>
            <p>Your secret has been created. Share the link below with the recipient. They will need the <strong>full link</strong> to view the secret.</p>
            <div class="mb-3">
                <label for="secretLink" class="form-label">Secret Link:</label>
                <div class="input-group">
                    <input type="text" id="secretLink" class="form-control" readonly>
                    <button class="btn btn-outline-success" id="copyLink">Copy</button>
                    <button class="btn btn-outline-primary" id="shareLink" data-bs-title="Share Link" data-bs-toggle="tooltip"><i class="bi bi-share-fill"></i></button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="js/encryption.js?v=<?php echo CURRENT_VERSION; ?>"></script>
    <script src="js/home.js?v=<?php echo CURRENT_VERSION; ?>"></script>
</body>
</html>