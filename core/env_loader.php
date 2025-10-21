<?php
// core/env_loader.php

// === CRITICAL FIX: Define missing constants for environments running older PHP versions or with Linter issues ===
// These flags are standard PHP constants but must be defined manually if the environment fails to recognize them.
if (!defined('FILE_IGNORE_EMPTY_LINES')) {
    define('FILE_IGNORE_EMPTY_LINES', 4);
}
if (!defined('FILE_SKIP_WHITE_SPACE')) {
    define('FILE_SKIP_WHITE_SPACE', 16);
}
// =============================================================================================================

// Define the absolute project path (the directory above 'core').
$appRootPath = dirname(__DIR__); 

// CRITICAL FIX: Determine the location of the .env file.
// The structure provided shows .env is one level above the application root folder.
// __DIR__ -> /project-folder/iCensus-ent-development-branch-MVC-/core
// dirname(__DIR__) -> /project-folder/iCensus-ent-development-branch-MVC- (App Root)
// dirname(dirname(__DIR__)) -> /project-folder (The Directory containing .env)
$actualProjectRoot = dirname($appRootPath); 

// Location 1: Outside the application folder (Confirmed by the user's latest image)
$envPath1 = $actualProjectRoot . '/.env'; 

// Location 2: Current application root (The second most likely fallback)
$envPath2 = $appRootPath . '/.env'; 

// Determine the actual path
$envPath = '';

if (file_exists($envPath1)) {
    $envPath = $envPath1;
} elseif (file_exists($envPath2)) {
    $envPath = $envPath2;
} else {
    // If no .env file is found in the expected locations, rely on fallbacks.
    $envPath = ''; 
}

// --- Load default config for backward compatibility/fallbacks ---
$dbConfigPath = $appRootPath . '/config/database.php';
$config = [];
if (file_exists($dbConfigPath)) {
    // Suppress potential PHP warnings if the file is valid PHP but can't be read immediately.
    // This loads the base DB configuration for fallbacks.
    $config = @require $dbConfigPath; 
    // Ensure $config is an array even if require fails or returns null
    if (!is_array($config)) {
        $config = [];
    }
}

// -------------------------------------------------------------------

// If the .env file exists, load environment variables from it
if (!empty($envPath) && file_exists($envPath)) {
    // CRITICAL FIX: Use @ operator to suppress warnings/errors from file() 
    // if the file exists but PHP cannot read it (e.g., empty or permissions issue).
    $lines = @file($envPath, FILE_IGNORE_EMPTY_LINES | FILE_SKIP_WHITE_SPACE);
    
    // Check if reading the file returned an array (successfully read)
    if ($lines !== false) {
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0 || empty(trim($line))) {
                continue;
            }

            // Split the line into KEY and VALUE
            // Use @ to suppress warning if explode fails (e.g., line without '=')
            @list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Strip quotes
            $value = preg_replace('/^([\'"])(.*)\1$/', '$2', $value);

            // Set the value in the $_ENV superglobal
            $_ENV[$key] = $value;
            // Also set in $_SERVER for maximum compatibility
            $_SERVER[$key] = $value;
        }
    }
} 
// -------------------------------------------------------------------

// Apply fallback values if the environment variables aren't set in $_ENV
// This ensures core services don't crash when DB config is missing.
$_ENV['DB_HOST'] = $_ENV['DB_HOST'] ?? $config['host'] ?? '127.0.0.1';
$_ENV['DB_NAME'] = $_ENV['DB_NAME'] ?? $config['dbname'] ?? 'icensus_db';
$_ENV['DB_USER'] = $_ENV['DB_USER'] ?? $config['user'] ?? 'root';
$_ENV['DB_PASSWORD'] = $_ENV['DB_PASSWORD'] ?? $config['password'] ?? '';
