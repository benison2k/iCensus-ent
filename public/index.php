<?php
// /public/index.php

// Start session and load essential files
require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/Database.php'; 

// A simple helper function to load our view files easily
function view($path, $data = []) {
    extract($data); 
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

// IMPORTANT: Adjust if your project is in the root directory of your web server
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

    // --- Authentication Routes ---
    case 'login':
        // Handles both showing the form (GET) and processing it (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AuthController())->login();
        } else {
            (new AuthController())->showLoginForm();
        }
        break;
    
    case 'logout':
        (new AuthController())->logout();
        break;

    // --- Dashboard Routes ---
    case 'dashboard':
        (new DashboardController())->index();
        break;
    
    case 'encoder-dashboard':
        (new DashboardController())->encoderDashboard();
        break;

    default:
        http_response_code(404);
        echo "<h1>404 Not Found</h1><p>The page '{$route}' could not be found.</p>";
        break;

    // --- Residents Routes ---
    case 'residents':
        (new ResidentController())->index();
        break;
    
    case 'residents/process': // New endpoint for AJAX calls
        (new ResidentController())->process();
        break;

    // --- Analytics Routes ---
    case 'analytics':
        (new AnalyticsController())->index();
        break;
    case 'analytics/data':
        (new AnalyticsController())->data();
        break;
    case 'analytics/layout':
        (new AnalyticsController())->getLayout();
        break;
    case 'analytics/layout/save':
        (new AnalyticsController())->saveLayout();
        break;

    // --- Settings Routes ---
    case 'settings':
        (new SettingsController())->index();
        break;
    case 'settings/process':
        (new SettingsController())->process();
        break;
    case 'settings/theme':
        (new SettingsController())->updateTheme();
        break;

    // --- System Admin Routes ---
    case 'sysadmin/dashboard':
        (new SysadminController())->dashboard();
        break;

    case 'sysadmin/users':
        (new SysadminController())->manageUsers();
        break;
    case 'sysadmin/users/process':
        (new SysadminController())->processUser();
        break;
    case 'sysadmin/users/get':
        (new SysadminController())->getUser();
        break;

    case 'sysadmin/db-tools':
        (new SysadminController())->dbTools();
        break;
    case 'sysadmin/db-tools/process':
        (new SysadminController())->processDbTools();
        break;
    
    case 'sysadmin/logs':
        (new SysadminController())->systemLogs();
        break;
}