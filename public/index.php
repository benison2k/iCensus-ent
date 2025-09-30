<?php
// /public/index.php

// Start session and load essential files
require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/Database.php'; // We'll need this in our controllers

// A simple helper function to load our view files easily
function view($path, $data = []) {
    extract($data); // This makes variables in the $data array available to the view
    require __DIR__ . "/../app/views/{$path}.php";
}

// Autoloader for Controllers
spl_autoload_register(function ($class_name) {
    $file = __DIR__ . "/../app/controllers/" . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// --- Basic Router ---
$request_uri = strtok($_SERVER["REQUEST_URI"], '?');

// IMPORTANT: Adjust this if your project is not in the root directory of your web server
$base_path = '/icensus-ent/iCensus-ent-overhaul-MVC-file-structure-implementation-/public'; 
$route = str_replace($base_path, '', $request_uri);
$route = trim($route, '/');
$route = empty($route) ? 'home' : $route;

// --- Routing Table ---
switch ($route) {
    case 'home':
        // For now, this just loads your original landing page
        require __DIR__ . '/../index.php'; 
        break;

    // We will add more routes here soon!
    // case 'login':
    //     (new AuthController())->showLoginForm();
    //     break;

    default:
        http_response_code(404);
        echo "<h1>404 Not Found</h1><p>The page '{$route}' could not be found.</p>";
        break;
}