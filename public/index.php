<?php
// /public/index.php

// Start session and load essential files
require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/Database.php'; 

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

// --- Router ---
$request_uri = strtok($_SERVER["REQUEST_URI"], '?');
$base_path = '/iCensus-ent/public';
$route = str_replace($base_path, '', $request_uri);
$route = trim($route, '/');
$route = empty($route) ? 'home' : $route;

// --- Routing Table ---
switch ($route) {
    case 'home':
        require __DIR__ . '/../index.php'; 
        break;

    // --- Auth Routes ---
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AuthController())->login();
        } else {
            (new AuthController())->showLoginForm();
        }
        break;
    case 'verify-otp':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AuthController())->verifyOtp();
        } else {
             header("Location: " . BASE_URL . "/login");
             exit;
        }
        break;
    case 'resend-otp':
        (new AuthController())->resendOtp();
        break;
    case 'logout':
        (new AuthController())->logout();
        break;
        
    case 'password/forgot':
        (new AuthController())->forgotPassword();
        break;
        
    case 'password/reset':
        (new AuthController())->resetPassword();
        break;

    // --- Dashboard Routes ---
    case 'dashboard':
        (new DashboardController())->index();
        break;
    case 'encoder-dashboard':
        (new DashboardController())->encoderDashboard();
        break;

    // --- Residents Routes ---
    case 'residents':
        (new ResidentController())->index();
        break;
    case 'residents/process':
        (new ResidentController())->process();
        break;
    case 'residents/find-by-address':
        (new ResidentController())->findByAddress();
        break;
    case 'residents/search-heads':
        (new ResidentController())->searchHeads();
        break;
    case 'residents/approve':
        (new ResidentController())->approve();
        break;
    case 'residents/approve-all':
        (new ResidentController())->approveAll();
        break;
    case 'residents/reject':
        (new ResidentController())->reject();
        break;

    // --- DYNAMIC CHART ROUTES ---
    case 'charts/save':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new ChartController())->save();
        }
        break;

    case 'charts/update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new ChartController())->update();
        }
        break;
    case 'charts/get':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            (new ChartController())->get();
        }
        break;
    case 'charts/preview':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new ChartController())->preview();
        }
        break;
    case 'charts/data':
        (new ChartController())->getData();
        break;
    case 'charts/user-charts':
        (new ChartController())->getUserCharts();
        break;
    case 'charts/delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        (new ChartController())->delete();
        }
        break;  

    // --- Analytics Routes ---
    case 'analytics':
        (new AnalyticsController())->index();
        break;
    case 'analytics/layout':
        (new AnalyticsController())->getLayout();
        break;
    case 'analytics/layout/save':
        (new AnalyticsController())->saveLayout();
        break;
    case 'analytics/layout/reset':
        (new AnalyticsController())->resetLayout();
        break;
    case 'analytics/report':
        (new AnalyticsController())->generateReport();
        break;
    case 'analytics/filtered-residents':
        (new AnalyticsController())->getFilteredResidents();
        break;
    
    // --- LEGACY ANALYTICS ROUTE (can be removed later) ---
    case 'analytics/data':
        (new AnalyticsController())->data();
        break;

    // --- REFACTORED Settings Routes ---
    case 'settings':
        (new SettingsController())->index();
        break;
    case 'settings/username': // NEW
        (new SettingsController())->updateUsername();
        break;
    case 'settings/email': // NEW
        (new SettingsController())->updateEmail();
        break;
    case 'settings/password': // NEW
        (new SettingsController())->updatePassword();
        break;
    // case 'settings/process': // OLD - REMOVED
    //     (new SettingsController())->process();
    //     break;
    case 'settings/theme':
        (new SettingsController())->updateTheme();
        break;
    case 'settings/verify-password':
        (new SettingsController())->verifyPassword();
        break;
    case 'settings/toggleTwoFA':
        (new SettingsController())->toggleTwoFA();
        break;
    case 'settings/verify-2fa-toggle-otp': 
        (new SettingsController())->verifyTwoFAToggleOtp();
        break;
    case 'settings/resendPasswordChangeOtp': 
        (new SettingsController())->resendPasswordChangeOtp();
        break;
    case 'settings/request-unbind-otp':
        (new SettingsController())->requestUnbindEmailOtp();
        break;
    case 'settings/confirm-unbind-email':
        (new SettingsController())->confirmUnbindEmail();
        break;
    // --- END Settings Routes ---

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
    case 'sysadmin/logs/mark-as-seen':
        (new SysadminController())->markLogAsSeen();
        break;
    case 'sysadmin/logs/mark-all-as-seen':
        (new SysadminController())->markAllLogsAsSeen();
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