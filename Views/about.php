<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo UI_TITLE; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-dark-5@1.1.3/dist/css/bootstrap-dark.min.css" rel="stylesheet" crossorigin="anonymous" media="(prefers-color-scheme: dark)">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="/css/main.css?v=<?php echo CURRENT_VERSION; ?>">
</head>
<body>

    <div class="container d-flex align-items-center main-content-container">
        <div class="container-sm inner-content-container" id="aboutContainer">
            <h1><?php echo UI_TITLE; ?></h1>
            <hr>
            <h2>About This Tool</h2>
            <p>This is a simple tool that allows you to create a secret message that can be shared with others.</p>
            <p>It works by encrypting your secret in a database, and providing a link that you can send to others to view the data.</p>
            <p>The secret is initially encrypted <i>on your device</i> before being sent to the server. This means that the server (or anyone with access to it) can never read the secret. Once the server receives the encrypted secret, it again encrypts it on the server side for additional protection before storing it.</p>
            <p>The link provided contains a special portion called a "hash" represented by (#). Everything after the hash is <i>not</i> sent to the server. The encryption key is contained in this hash, and is used by JavaScript <i>on your device</i> to decrypt the secret.</p>
            <p>Once the secret is viewed the maximum number of time, or the expiration time specified has elapsed, it is deleted from the database and can no longer be accessed.</p>
            <p>For more information, please visit the <a href="https://github.com/domkirby/SecretShare" target="_blank">GitHub repository</a>.</p>
            <div class="mb-3">
                <a href="/" class="btn btn-primary">Create a New Secret</a>
            </div>
            <p><button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#thirdPartyCollapse" aria-expanded="false" aria-controls="thirdPartyCollapse">Third Party Credits</button></p>
            <div class="collapse" id="thirdPartyCollapse">
                <div class="card card-body">
                    This tool uses the following third-party libraries:
                    <ul>
                        <li><a href="https://getbootstrap.com/" target="_blank">Bootstrap</a></li>
                        <li><a href="https://github.com/bramus/router" target="_blank">Bramus Router</a></li>
                        <li><a href="https://developer.mozilla.org/en-US/docs/Web/API/Web_Crypto_API" target="_blank">Web Crypto API (built into your browser)</a></li>
                    </ul>
                    <i>Written with the help of GitHub Copilot</i>
                </div>
            </div>
        </div>
    </div>
</body>
</html>