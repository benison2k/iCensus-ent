<?php
require_once __DIR__ . '/../core/init.php';

echo "<pre>";
echo "Checking Environment Variables...\n";

if (isset($_ENV['RESET_USERNAME'])) {
    echo "✅ RESET_USERNAME is set to: " . $_ENV['RESET_USERNAME'];
} else {
    echo "❌ RESET_USERNAME is MISSING!";
}
echo "</pre>";