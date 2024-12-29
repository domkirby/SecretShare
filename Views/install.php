<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecretShare Installer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-dark-5@1.1.3/dist/css/bootstrap-dark.min.css" rel="stylesheet" crossorigin="anonymous" media="(prefers-color-scheme: dark)">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="/css/main.css?v=<?php echo CURRENT_VERSION; ?>">
</head>
<body>

    <div class="container d-flex align-items-center main-content-container">
        <div class="container-sm inner-content-container" id="createSecretContainer">
            <h1>SecretShare Installer</h1>
            <p>Welcome to the SecretShare installer. This tool will create the necessary database tables for the SecretShare service.</p>
            <p>After you finish installing, you <strong>must</strong> set the INSTALLED constant in _config.php to true.</p>
            <p>Before running this installer, ensure that you have set the proper variables in _config.php.</p>
            <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger" id="errors">
                <?php echo htmlspecialchars($_GET['error'] ?? ''); ?>
            </div>
            <?php endif; ?>
            <form action="/install" method="post">
                <input type="hidden" name="token" value="<?php echo $this->CSRF_TOKEN; ?>">
                <button type="submit" class="btn btn-primary btn-lg">Install</button>
            </form>
        </div>
    </div>
</body>
</html>