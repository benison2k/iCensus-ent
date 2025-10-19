<?php
// /public/index.php

require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/Database.php'; 

function view($path, $data = []) {
    if (!is_array($data)) {
        $data = [];
    }
    extract($data); 
    require __DIR__ . "/../app/views/{$path}.php";
}

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

$request_uri = strtok($_SERVER["REQUEST_URI"], '?');
$base_path = '/iCensus-ent/public';
$route = str_replace($base_path, '', $request_uri);
$route = trim($route, '/');
$route = empty($route) ? 'home' : $route;

switch ($route) {
    case 'home':
        require __DIR__ . '/../index.php'; 
        break;

    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AuthController())->login();
        } else {
            (new AuthController())->showLoginForm();
        }
        break;
    case 'logout':
        (new AuthController())->logout();
        break;

    case 'verify-otp':
        (new AuthController())->verifyOtpAndLogin();
        break;

    case 'dashboard':
        (new DashboardController())->index();
        break;
    case 'encoder-dashboard':
        (new DashboardController())->encoderDashboard();
        break;

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

    case 'charts/save':
    case 'charts/update':
    case 'charts/preview':
    case 'charts/delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = basename($route);
            (new ChartController())->$action();
        }
        break;
    case 'charts/get':
    case 'charts/data':
    case 'charts/user-charts':
        $action = basename($route);
        (new ChartController())->$action();
        break;
    
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
    case 'analytics/data':
        (new AnalyticsController())->data();
        break;

    case 'settings':
        (new SettingsController())->index();
        break;
    case 'settings/process':
    case 'settings/theme':
    case 'settings/verify-password':
        $action = str_replace('-', '_', basename($route));
        (new SettingsController())->$action();
        break;

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
    
    case 'about':
        view('about/index', ['theme' => $_SESSION['user']['theme'] ?? 'light']);
        break;
    
    default:
        http_response_code(404);
        echo "<h1>404 Not Found</h1><p>The page '{$route}' could not be found.</p>";
        break;
}