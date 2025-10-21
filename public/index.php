<?php
// /public/index.php

// Start session and load essential files
require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/Database.php'; 
require_once __DIR__ . '/../core/Auth.php'; // Ensure Auth is loaded here

// Helper function to load view files
function view($path, $data = []) {
    // --- FIX: Ensure $data is always an array ---
    if (!is_array($data)) {
        $data = [];
    }
    extract($data); 
    require __DIR__ . "/../app/views/{$path}.php";
}

// Autoloader for Controllers and Models
spl_autoload_register(function ($class_name) {
    $controller_file = __DIR__ . "/../app/controllers/" . $class_name . '.php';
    if (file_exists($controller_file)) {
        require_once $controller_file;
        return;
    }
    $model_file = __DIR__ . "/../app/models/" . $class_name . '.php';
    if (file_exists($model_file)) {
        require_once $model_file;
    }
});


// --- APPLICATION BOOTSTRAP (Dependency Setup) ---

// 1. Load the database configuration from the environment/defaults
// NOTE: We assume DB details are loaded via the .env system into $_ENV in core/init.php
$db_config = [
    'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'dbname' => $_ENV['DB_NAME'] ?? 'icensus_db',
    'user' => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset' => 'utf8mb4',
];

// 2. Instantiate core services
$db = new Database($db_config);
// CRITICAL: Set the global $db for the legacy log_action function only
// In a proper refactor, this global should be removed entirely.
$GLOBALS['db'] = $db; 

$auth = new Auth($db);
// We assume other core controllers (Dashboard, Resident, etc.) also require the $db instance.

// --- Router ---
$request_uri = strtok($_SERVER["REQUEST_URI"], '?');
$base_path = '/iCensus-ent/public';
$route = str_replace($base_path, '', $request_uri);
$route = trim($route, '/');
$route = empty($route) ? 'home' : $route;

// --- Routing Table (Controllers now receive dependencies) ---
switch ($route) {
    case 'home':
        require __DIR__ . '/../index.php'; 
        break;

    // --- Auth Routes ---
    case 'login':
        // Instantiate controller with necessary dependencies
        $controller = new AuthController($db, $auth); 
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->login();
        } else {
            $controller->showLoginForm();
        }
        break;
    case 'verify-otp':
        $controller = new AuthController($db, $auth); 
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->verifyOtp();
        } else {
             header("Location: " . BASE_URL . "/login");
             exit;
        }
        break;
    case 'resend-otp':
        (new AuthController($db, $auth))->resendOtp(); 
        break;
    case 'logout':
        (new AuthController($db, $auth))->logout(); 
        break;
        
    // --- ADDED PASSWORD RESET ROUTES ---
    case 'password/forgot':
        (new AuthController($db, $auth))->forgotPassword(); 
        break;
        
    case 'password/reset':
        (new AuthController($db, $auth))->resetPassword(); 
        break;
    // --- END ADDED ---

    // --- Dashboard Routes ---
    // NOTE: Requires updating DashboardController constructor
    case 'dashboard':
        (new DashboardController($db))->index();
        break;
    case 'encoder-dashboard':
        (new DashboardController($db))->encoderDashboard();
        break;

    // --- Residents Routes ---
    // NOTE: Requires updating ResidentController constructor
    case 'residents':
        (new ResidentController($db))->index();
        break;
    case 'residents/process':
        (new ResidentController($db))->process();
        break;
    case 'residents/find-by-address':
        (new ResidentController($db))->findByAddress();
        break;
    case 'residents/search-heads':
        (new ResidentController($db))->searchHeads();
        break;
    case 'residents/approve':
        (new ResidentController($db))->approve();
        break;
    case 'residents/approve-all':
        (new ResidentController($db))->approveAll();
        break;
    case 'residents/reject':
        (new ResidentController($db))->reject();
        break;

    // --- DYNAMIC CHART ROUTES ---
    // NOTE: Requires updating ChartController constructor
    case 'charts/save':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new ChartController($db))->save();
        }
        break;

    case 'charts/update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new ChartController($db))->update();
        }
        break;
    case 'charts/get':
    case 'charts/user-charts': // Combined for brevity
        (new ChartController($db))->get();
        break;
    case 'charts/preview':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new ChartController($db))->preview();
        }
        break;
    case 'charts/data':
        (new ChartController($db))->getData();
        break;
    case 'charts/delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        (new ChartController($db))->delete();
        }
        break;
        
    // --- Analytics Routes ---
    // NOTE: Requires updating AnalyticsController constructor
    case 'analytics':
        (new AnalyticsController($db))->index();
        break;
    case 'analytics/layout':
        (new AnalyticsController($db))->getLayout();
        break;
    case 'analytics/layout/save':
        (new AnalyticsController($db))->saveLayout();
        break;
    case 'analytics/layout/reset':
        (new AnalyticsController($db))->resetLayout();
        break;
    case 'analytics/report':
        (new AnalyticsController($db))->generateReport();
        break;
    case 'analytics/filtered-residents':
        (new AnalyticsController($db))->getFilteredResidents();
        break;
    case 'analytics/data':
        (new AnalyticsController($db))->data();
        break;

    // --- Settings Routes ---
    // NOTE: Requires updating SettingsController constructor
    case 'settings':
        (new SettingsController($db, $auth))->index();
        break;
    case 'settings/process':
        (new SettingsController($db, $auth))->process();
        break;
    case 'settings/theme':
        (new SettingsController($db, $auth))->updateTheme();
        break;
    case 'settings/verify-password':
        (new SettingsController($db, $auth))->verifyPassword();
        break;
    case 'settings/toggleTwoFA':
        (new SettingsController($db, $auth))->toggleTwoFA();
        break;
    case 'settings/verify-2fa-toggle-otp': 
        (new SettingsController($db, $auth))->verifyTwoFAToggleOtp();
        break;
    case 'settings/resendPasswordChangeOtp': 
        (new SettingsController($db, $auth))->resendPasswordChangeOtp();
        break;

    // --- System Admin Routes ---
    // NOTE: Requires updating SysadminController constructor
    case 'sysadmin/dashboard':
        (new SysadminController($db))->dashboard();
        break;
    case 'sysadmin/users':
        (new SysadminController($db))->manageUsers();
        break;
    case 'sysadmin/users/process':
        (new SysadminController($db))->processUser();
        break;
    case 'sysadmin/users/get':
        (new SysadminController($db))->getUser();
        break;
    case 'sysadmin/db-tools':
        (new SysadminController($db))->dbTools();
        break;
    case 'sysadmin/db-tools/process':
        (new SysadminController($db))->processDbTools();
        break;
    case 'sysadmin/logs':
        (new SysadminController($db))->systemLogs();
        break;
    case 'sysadmin/logs/mark-as-seen':
        (new SysadminController($db))->markLogAsSeen();
        break;
    case 'sysadmin/logs/mark-all-as-seen':
        (new SysadminController($db))->markAllLogsAsSeen();
        break;
    
    // --- About Page Route ---
    case 'about':
        view('about/index', ['theme' => $_SESSION['user']['theme'] ?? 'light']);
        break;
    
    default:
        http_response_code(404);
        echo "<h1>404 Not Found</h1><p>The page '{$route}' could not be found.</p>";
        break;
}
