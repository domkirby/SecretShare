<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo UI_TITLE; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="/css/main.css?v=<?php echo CURRENT_VERSION; ?>">
</head>
<body>

    <div class="container d-flex align-items-center main-content-container">
        <div class="container-sm inner-content-container" id="aboutContainer">
            <h1><?php echo UI_TITLE; ?></h1>
            <hr>
            <h2>Not Installed</h2>
            <div class="alert alert-danger">
                <p>SecretShare has not been installed. Please run the installer by visiting <a href="/install">/install</a>.</p>
            </div>
        </div>
    </div>
</body>
</html>