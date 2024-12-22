<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo UI_TITLE; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="/css/main.css">
</head>
<body>

    <div class="container d-flex align-items-center main-content-container">
        <div class="container-sm inner-content-container" id="retrieveSecretContainer">
            <h1><?php echo UI_TITLE; ?></h1>
            <p><?php echo UI_RETRIEVE_EXPLAINER; ?></p>
            <div class="alert alert-danger" id="errors" style="display: none;">

            </div>
            <form action="#" id="retrieveSecretForm">
                <div class="mb-3">
                    <input type="hidden" name="secretId" id="secretId" value="<?php echo $secretId; ?>">
                    <input type="hidden" name="csrfToken" id="csrfToken" value="<?php echo $this->CSRF_TOKEN; ?>">
                    <button type="submit" class="btn btn-primary btn-lg" id="submitButton">Retrieve Secret</button>
                    <div class="spinner-border" role="status" id="loading" style="display: none;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </form>
        </div>
        <div class="container-sm inner-content-container" id="decryptedSecretContainer" style="display: none;">
            <h1>Secret Retrieved!</h1>
            <p>Your secret has been retreived. Please be sure to copy it and store it securely, as it will <strong>not</strong> be permanently stored here.</p>
            <div class="alert alert-danger" id="errorsTwo" style="display: none;">

            </div>
            <div class="alert alert-success" id="secretDeleted" style="display: none;">
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
    <script src="/js/encryption.js"></script>
    <script>
        const csrfToken = "<?php echo $this->CSRF_TOKEN; ?>";
    </script>
    <script src="/js/retrieve.js"></script>
</body>
</html>