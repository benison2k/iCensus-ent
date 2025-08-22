<?php
// index.php (project root)
$config = require __DIR__ . '/config/database.php';
require __DIR__ . '/core/Database.php';

try {
    $db = new Database($config);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>iCensus Redirect</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                background-color: #f9f9f9;
                color: #333;
            }
            .container {
                text-align: center;
            }
            h1 {
                color: green;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>This is a System message from iCensus - Database connected successfully!</h1>
            <p>Redirecting to login page in <span id="countdown">3</span> seconds...</p>
        </div>

        <script>
            let seconds = 3;
            const countdownEl = document.getElementById('countdown');

            const interval = setInterval(() => {
                seconds--;
                countdownEl.textContent = seconds;
                if (seconds <= 0) {
                    clearInterval(interval);
                    window.location.href = 'pages/login.php';
                }
            }, 1000);
        </script>
    </body>
    </html>
    <?php
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
}
