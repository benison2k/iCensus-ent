<?php
// /public/index.php

// Start session and load essential files
require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/Database.php'; 

// Helper function to load view files
function view($path, $data = []) {
    extract($data); 
    require __DIR__ . "/../app/views/{$path}.php";
}

// Autoloader for Controllers and Models
spl_autoload_register(function ($class_name) {
    $paths = [
        __DIR__ . "/../app/controllers/" . $class_name . '.php',
        __DIR__ . "/../app/models/" . $class_name . '.php'
    ];
    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// --- Router ---
$request_uri = strtok($_SERVER["REQUEST_URI"], '?');
$base_path = '/iCensus-ent/public';
$route = trim(str_replace($base_path, '', $request_uri), '/');
$route = empty($route) ? 'home' : $route;

// --- Routing Table ---
switch ($route) {
    case 'home':
        require __DIR__ . '/../index.php'; 
        break;

    // --- Auth Routes ---
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { (new AuthController())->login(); } 
        else { (new AuthController())->showLoginForm(); }
        break;
    case 'logout':
        (new AuthController())->logout();
        break;

    // --- Dashboard Routes ---
    case 'dashboard': (new DashboardController())->index(); break;
    case 'encoder-dashboard': (new DashboardController())->encoderDashboard(); break;

    // --- Residents Routes ---
    case 'residents': (new ResidentController())->index(); break;
    case 'residents/process': (new ResidentController())->process(); break;
    case 'residents/find-by-address': (new ResidentController())->findByAddress(); break;
    case 'residents/search-heads': (new ResidentController())->searchHeads(); break;
    case 'residents/approve': (new ResidentController())->approve(); break;
    case 'residents/approve-all': (new ResidentController())->approveAll(); break;
    case 'residents/reject': (new ResidentController())->reject(); break;

    // --- Analytics Routes ---
    case 'analytics': (new AnalyticsController())->index(); break;
    
    // --- NEW DYNAMIC ANALYTICS ROUTES ---
    case 'analytics/charts': (new AnalyticsController())->getCharts(); break;
    case 'analytics/dynamic-data': (new AnalyticsController())->dynamicData(); break;
    case 'analytics/save-chart': (new AnalyticsController())->saveChart(); break;
    
    // --- OLD & OTHER ANALYTICS ROUTES ---
    case 'analytics/data': (new AnalyticsController())->data(); break;
    case 'analytics/layout': (new AnalyticsController())->getLayout(); break;
    case 'analytics/layout/save': (new AnalyticsController())->saveLayout(); break;
    case 'analytics/layout/reset': (new AnalyticsController())->resetLayout(); break;
    case 'analytics/report': (new AnalyticsController())->generateReport(); break;
    case 'analytics/filtered-residents': (new AnalyticsController())->getFilteredResidents(); break;

    // --- Settings Routes ---
    case 'settings': (new SettingsController())->index(); break;
    case 'settings/process': (new SettingsController())->process(); break;
    case 'settings/theme': (new SettingsController())->updateTheme(); break;
    case 'settings/verify-password': (new SettingsController())->verifyPassword(); break;

    // --- System Admin Routes ---
    case 'sysadmin/dashboard': (new SysadminController())->dashboard(); break;
    case 'sysadmin/users': (new SysadminController())->manageUsers(); break;
    case 'sysadmin/users/process': (new SysadminController())->processUser(); break;
    case 'sysadmin/users/get': (new SysadminController())->getUser(); break;
    case 'sysadmin/db-tools': (new SysadminController())->dbTools(); break;
    case 'sysadmin/db-tools/process': (new SysadminController())->processDbTools(); break;
    case 'sysadmin/logs': (new SysadminController())->systemLogs(); break;
    case 'sysadmin/logs/mark-as-seen': (new SysadminController())->markLogAsSeen(); break;
    case 'sysadmin/logs/mark-all-as-seen': (new SysadminController())->markAllLogsAsSeen(); break;
    
    // --- About Page Route ---
    case 'about':
        view('about/index', ['theme' => $_SESSION['user']['theme'] ?? 'light']);
        break;
    
    default:
        http_response_code(404);
        echo "<h1>404 Not Found</h1><p>The page '{$route}' could not be found.</p>";
        break;
}
