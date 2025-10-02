<?php
// app/controllers/AnalyticsController.php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../models/Analytics.php';

class AnalyticsController {

    private function checkAuth() {
        if (!isset($_SESSION['user'])) { header('Location: /iCensus-ent/public/login'); exit; }
        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $GLOBALS['db'] = $db; // For logging
        $auth = new Auth($db);
        $auth->refreshUserSession($_SESSION['user']['id']);
    }

    public function index() {
        $this->checkAuth();
        $db = new Database(require __DIR__ . '/../../config/database.php');
        $analyticsModel = new Analytics($db);
        
        $filterOptions = $analyticsModel->getFilterOptions();

        $data = [
            'user' => $_SESSION['user'],
            'theme' => $_SESSION['user']['theme'] ?? 'light',
            'puroks' => $filterOptions['puroks'],
            'statuses' => $filterOptions['statuses'],
            'available_columns' => [
                'full_name' => 'Full Name',
                'address' => 'Full Address',
                'dob' => 'Date of Birth',
                'age' => 'Age',
                'gender' => 'Gender',
                'civil_status' => 'Civil Status',
                'contact_number' => 'Contact Number',
                'email' => 'Email',
                'blood_type' => 'Blood Type',
                'nationality' => 'Nationality',
                'status' => 'Resident Status',
                'date_added' => 'Date Added'
            ],
            'available_charts' => [
                'gender' => 'Gender Distribution',
                'age' => 'Age Groups',
                'purok' => 'Population by Purok',
                'civil_status' => 'Civil Status',
                'blood_type' => 'Blood Type',
                'nationality' => 'Nationality',
            ]
        ];
        view('analytics/index', $data);
    }

    public function data() {
        $this->checkAuth();
        header('Content-Type: application/json');
        
        $metric = $_GET['metric'] ?? '';
        $filters = [
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null,
            'purok' => $_GET['purok'] ?? null,
            'status' => $_GET['status'] ?? null,
            'gender' => $_GET['gender'] ?? null,
            'civil_status' => $_GET['civil_status'] ?? null
        ];

        $db = new Database(require __DIR__ . '/../../config/database.php');
        $analyticsModel = new Analytics($db);
        echo json_encode($analyticsModel->getChartData($metric, array_filter($filters))); // array_filter removes nulls
        exit;
    }

    public function getLayout() {
        $this->checkAuth();
        header('Content-Type: application/json');
        $db = new Database(require __DIR__ . '/../../config/database.php');
        $analyticsModel = new Analytics($db);
        echo json_encode($analyticsModel->getLayoutForUser($_SESSION['user']['id']));
        exit;
    }

    public function saveLayout() {
        $this->checkAuth();
        header('Content-Type: application/json');
        $layout_data = file_get_contents('php://input');
        $db = new Database(require __DIR__ . '/../../config/database.php');
        $analyticsModel = new Analytics($db);
        $success = $analyticsModel->saveLayoutForUser($_SESSION['user']['id'], $layout_data);
        if($success) {
            log_action('INFO', 'ANALYTICS_LAYOUT_SAVE', 'User saved their analytics dashboard layout.');
        }
        echo json_encode(['status' => $success ? 'success' : 'error']);
        exit;
    }

    public function resetLayout() {
        $this->checkAuth();
        header('Content-Type: application/json');
        $db = new Database(require __DIR__ . '/../../config/database.php');
        $analyticsModel = new Analytics($db);
        $success = $analyticsModel->deleteLayoutForUser($_SESSION['user']['id']);
        if($success) {
            log_action('INFO', 'ANALYTICS_LAYOUT_RESET', 'User reset their analytics dashboard layout to default.');
        }
        echo json_encode(['status' => $success ? 'success' : 'error']);
        exit;
    }

    public function generateReport() {
        $this->checkAuth();
        $db = new Database(require __DIR__ . '/../../config/database.php');
        $pdo = $db->getPdo();

        $sort_by = $_POST['sort_by'] ?? 'last_name';
        $sort_order = $_POST['sort_order'] ?? 'ASC';
        $selected_columns = $_POST['columns'] ?? [];
        $selected_charts = $_POST['charts'] ?? [];
        $font_size = $_POST['font_size'] ?? '12px';
        $orientation = $_POST['orientation'] ?? 'portrait';

        $allowed_sort_columns = ['last_name', 'first_name', 'date_added', 'dob'];
        if (!in_array($sort_by, $allowed_sort_columns)) {
            $sort_by = 'last_name';
        }
        $sort_order = ($sort_order === 'DESC') ? 'DESC' : 'ASC';

        $all_columns = [
            'full_name' => ['label' => 'Full Name', 'sql' => "CONCAT(first_name, ' ', last_name)"],
            'address' => ['label' => 'Full Address', 'sql' => "CONCAT(house_no, ' ', street, ', Purok ', purok)"],
            'dob' => ['label' => 'Date of Birth', 'sql' => 'dob'],
            'age' => ['label' => 'Age', 'sql' => 'TIMESTAMPDIFF(YEAR, dob, CURDATE())'],
            'gender' => ['label' => 'Gender', 'sql' => 'gender'],
            'civil_status' => ['label' => 'Civil Status', 'sql' => 'civil_status'],
            'contact_number' => ['label' => 'Contact Number', 'sql' => 'contact_number'],
            'email' => ['label' => 'Email', 'sql' => 'email'],
            'blood_type' => ['label' => 'Blood Type', 'sql' => 'blood_type'],
            'nationality' => ['label' => 'Nationality', 'sql' => 'nationality'],
            'status' => ['label' => 'Resident Status', 'sql' => 'status'],
            'date_added' => ['label' => 'Date Added', 'sql' => 'date_added']
        ];

        $columns_to_select = [];
        $report_headers = [];
        if (!empty($selected_columns)) {
            foreach ($selected_columns as $col) {
                if (array_key_exists($col, $all_columns)) {
                    $columns_to_select[$col] = $all_columns[$col]['sql'] . " AS " . $col;
                    $report_headers[$col] = $all_columns[$col]['label'];
                }
            }
        }

        if (!empty($columns_to_select)) {
            $sql = "SELECT " . implode(", ", $columns_to_select) . " FROM residents WHERE approval_status = 'approved' ORDER BY $sort_by $sort_order";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $results = [];
        }

        $chart_data = [];
        if (!empty($selected_charts)) {
            $stmt_all = $pdo->query("SELECT dob, gender, civil_status, blood_type, nationality, purok FROM residents WHERE approval_status = 'approved'");
            $all_residents = $stmt_all->fetchAll(PDO::FETCH_ASSOC);

            foreach ($selected_charts as $metric) {
                $data = [];
                switch ($metric) {
                    case 'gender':
                        foreach ($all_residents as $r) $data[$r['gender'] ?: 'Unknown'] = ($data[$r['gender'] ?: 'Unknown'] ?? 0) + 1;
                        break;
                    case 'age':
                        $ageGroups = ['0-17' => 0, '18-35' => 0, '36-59' => 0, '60+' => 0];
                        foreach ($all_residents as $r) {
                            $age = $r['dob'] ? (new DateTime($r['dob']))->diff(new DateTime('today'))->y : null;
                            if ($age === null) continue;
                            if ($age <= 17) $ageGroups['0-17']++;
                            elseif ($age <= 35) $ageGroups['18-35']++;
                            elseif ($age <= 59) $ageGroups['36-59']++;
                            else $ageGroups['60+']++;
                        }
                        $data = $ageGroups;
                        break;
                    case 'civil_status':
                        foreach ($all_residents as $r) $data[trim($r['civil_status']) ?: 'Unknown'] = ($data[trim($r['civil_status']) ?: 'Unknown'] ?? 0) + 1;
                        break;
                    case 'blood_type':
                        foreach ($all_residents as $r) $data[trim($r['blood_type']) ?: 'Unknown'] = ($data[trim($r['blood_type']) ?: 'Unknown'] ?? 0) + 1;
                        break;
                    case 'nationality':
                        foreach ($all_residents as $r) $data[trim($r['nationality']) ?: 'Unknown'] = ($data[trim($r['nationality']) ?: 'Unknown'] ?? 0) + 1;
                        break;
                    case 'purok':
                        foreach ($all_residents as $r) $data[trim($r['purok']) ?: 'Unknown'] = ($data[trim($r['purok']) ?: 'Unknown'] ?? 0) + 1;
                        break;
                }
                $chart_data[$metric] = $data;
            }
        }
        
        log_action('INFO', 'REPORT_GENERATED', 'User generated a custom report.');
        
        $reportData = [
            'results' => $results,
            'report_headers' => $report_headers,
            'selected_charts' => $selected_charts,
            'chart_data' => $chart_data,
            'font_size' => $font_size,
            'orientation' => $orientation
        ];

        view('analytics/report', $reportData);
    }
}