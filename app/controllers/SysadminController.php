<?php
// app/controllers/SysadminController.php

require_once __DIR__ . '/../../core/functions.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Log.php';
require_once __DIR__ . '/../../core/Database.php';


class SysadminController {

    private function requireSysadmin() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'System Admin') {
            header("Location: /iCensus-ent/public/login");
            exit;
        }
    }

    public function dashboard() {
        $this->requireSysadmin();

        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $logModel = new Log($db);
        
        $new_log_count = $logModel->getUnseenLogCount();

        $data = [
            'user' => $_SESSION['user'],
            'theme' => $_SESSION['user']['theme'] ?? 'light',
            'new_log_count' => $new_log_count
        ];

        view('sysadmin/dashboard', $data);
    }

    public function manageUsers() {
        $this->requireSysadmin();
        
        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $userModel = new User($db);

        $userData = $userModel->getManageableUsers();

        $data = [
            'user' => $_SESSION['user'],
            'theme' => $_SESSION['user']['theme'] ?? 'light',
            'all_users' => $userData,
            'assignable_roles' => $userModel->getAssignableRoles(),
            'modalMessage' => $_SESSION['modal']['message'] ?? '',
            'modalType' => $_SESSION['modal']['type'] ?? ''
        ];
        
        unset($_SESSION['modal']);

        view('sysadmin/manage_users', $data);
    }

    public function processUser() {
        $this->requireSysadmin();
        
        // --- NEW: CSRF Check ---
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Security Token Error.']);
                exit;
            }
            $_SESSION['modal'] = ['message' => 'Security Token Expired.', 'type' => 'error'];
            header("Location: /iCensus-ent/public/sysadmin/users");
            exit;
        }
        // -----------------------
        
        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $GLOBALS['db'] = $db;
        $userModel = new User($db);
        
        $action = $_REQUEST['action'] ?? 'save';
        $is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

        try {
            unset($_POST['csrf_token']);

            if ($action === 'save') {
                $is_new_user = empty($_POST['user_id']);

                if (!$is_new_user) {
                    $old_data = $userModel->find($_POST['user_id']);
                }

                $user_id = $userModel->save($_POST);
                $new_data = $userModel->find($user_id);
                
                if ($is_new_user) {
                    log_action('INFO', 'USER_CREATE', "New user account '" . htmlspecialchars($_POST['username']) . "' (ID#{$user_id}) was created.");
                    $message = 'User created successfully.';
                } else {
                    unset($old_data['password'], $new_data['password']);
                    $changes = array_diff_assoc($new_data, $old_data);
                    $log_details = "Updated user account for '" . htmlspecialchars($new_data['username']) . "'.";

                    if (!empty($changes)) {
                        $log_details .= " Changes: ";
                        foreach($changes as $key => $value) {
                            $log_details .= "{$key} changed from '{$old_data[$key]}' to '{$value}'; ";
                        }
                        $log_details = rtrim($log_details, '; ');
                        $log_details .= ".";
                    } else if (!empty($_POST['password'])) {
                        $log_details .= " Password was changed.";
                    } else {
                        $log_details .= " No data fields were changed.";
                    }
                    log_action('INFO', 'USER_UPDATE', $log_details);
                    $message = 'User updated successfully.';
                }
                
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['status' => 'success', 'message' => $message, 'user' => $new_data, 'is_new' => $is_new_user]);
                    exit;
                }
                $_SESSION['modal'] = ['message' => $message, 'type' => 'success'];

            } elseif ($action === 'delete') {
                $user_id_to_delete = $_POST['user_id'];
                $user_to_delete = $userModel->find($user_id_to_delete);
                if ($user_to_delete) {
                    $userModel->delete($user_id_to_delete);
                    $log_message = "User account '" . htmlspecialchars($user_to_delete['username']) . "' (ID#{$user_id_to_delete}) was deleted.";
                    log_action('INFO', 'USER_DELETE', $log_message);
                    
                    if ($is_ajax) {
                        header('Content-Type: application/json');
                        echo json_encode(['status' => 'success', 'message' => 'User deleted successfully.']);
                        exit;
                    }
                    $_SESSION['modal'] = ['message' => 'User deleted successfully.', 'type' => 'success'];
                } else {
                     $_SESSION['modal'] = ['message' => 'User not found for deletion.', 'type' => 'error'];
                }
            }
        } catch (Exception $e) {
            log_action('ERROR', 'USER_MANAGE_ERROR', 'Error processing user: ' . $e->getMessage());
            if ($is_ajax) {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
                exit;
            }
            $_SESSION['modal'] = ['message' => 'An error occurred: ' . $e->getMessage(), 'type' => 'error'];
        }
        
        header("Location: /iCensus-ent/public/sysadmin/users");
        exit;
    }

    public function getUser() {
        $this->requireSysadmin();
        header('Content-Type: application/json');

        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $userModel = new User($db);

        $user = $userModel->find($_GET['user_id'] ?? 0);
        echo json_encode($user ? ['status'=>'success', 'user'=>$user] : ['status'=>'error']);
        exit;
    }    

    public function dbTools() {
        $this->requireSysadmin();
    
        $data = [
            'user' => $_SESSION['user'],
            'theme' => $_SESSION['user']['theme'] ?? 'light',
            'modalMessage' => $_SESSION['modal']['message'] ?? '',
            'modalType' => $_SESSION['modal']['type'] ?? ''
        ];
        unset($_SESSION['modal']);
    
        view('sysadmin/db_tools', $data);
    }
    
    public function processDbTools() {
        $this->requireSysadmin();
        
        // --- NEW: CSRF Check Added ---
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            $_SESSION['modal'] = ['message' => 'Security Token Expired.', 'type' => 'error'];
            header("Location: /iCensus-ent/public/sysadmin/db-tools");
            exit;
        }
        // -----------------------------
        
        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $GLOBALS['db'] = $db;
        $action = $_POST['action'] ?? '';
    
        try {
            if ($action === 'backup_db') {
                $backupDir = __DIR__ . '/../../backups/';
                if (!is_dir($backupDir)) {
                    mkdir($backupDir, 0777, true);
                }
                $backupFile = $backupDir . 'icensus_db_' . date('Y-m-d_H-i-s') . '.sql';

                $mysql_path = "C:\\xampp\\mysql\\bin\\mysqldump";
                $command = sprintf(
                    '"%s" --user=%s --password=%s --host=%s %s > %s',
                    $mysql_path,
                    $config['user'],
                    $config['password'],
                    $config['host'],
                    $config['dbname'],
                    $backupFile
                );
                
                system($command, $return_var);

                if ($return_var === 0) {
                    log_action('INFO', 'DB_BACKUP', 'Database backup successful.');
                    $_SESSION['modal'] = ['message' => 'Database backup successful.', 'type' => 'success'];
                } else {
                     throw new Exception("Backup command failed with return code: $return_var");
                }

            } else {
                throw new Exception('Invalid action.');
            }
        } catch (Exception $e) {
            log_action('ERROR', 'DB_BACKUP_FAIL', 'Database backup failed: ' . $e->getMessage());
            $_SESSION['modal'] = ['message' => 'An error occurred during backup. Check system logs for details.', 'type' => 'error'];
        }
    
        header("Location: /iCensus-ent/public/sysadmin/db-tools");
        exit;
    }

    public function systemLogs() {
        $this->requireSysadmin();
    
        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $logModel = new Log($db);
        $userModel = new User($db);
    
        $filter = $_GET['filter'] ?? 'all';
        $pageSize = $_GET['pageSize'] ?? 10;
        $sort_by = $_GET['sort_by'] ?? 'timestamp';
        $sort_order = $_GET['sort_order'] ?? 'DESC';
        $page = $_GET['page'] ?? 1;
        $user_id = $_GET['user_id'] ?? '';
        $level = $_GET['level'] ?? '';
        $search = $_GET['search'] ?? '';

        $log_actions = [];
        switch ($filter) {
            case 'auth': $log_actions = ['USER_LOGIN_SUCCESS', 'USER_LOGIN_FAIL', 'USER_LOGOUT']; break;
            case 'data': $log_actions = ['RESIDENT_CREATE', 'RESIDENT_UPDATE', 'RESIDENT_DELETE']; break;
            case 'user_management': $log_actions = ['USER_CREATE', 'USER_UPDATE', 'USER_DELETE']; break;
            case 'system': $log_actions = ['SYSTEM_ERROR', 'DB_ERROR', 'SETTINGS_UPDATE', 'DB_BACKUP', 'DB_BACKUP_FAIL']; break;
        }
    
        $logData = $logModel->getLogs([
            'actions' => $log_actions,
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? '',
            'user_id' => $user_id,
            'level' => $level,
            'search' => $search,
            'page' => $page,
            'pageSize' => $pageSize,
            'sort_by' => $sort_by,
            'sort_order' => $sort_order
        ]);
        
        $data = [
            'user' => $_SESSION['user'],
            'theme' => $_SESSION['user']['theme'] ?? 'light',
            'logs' => $logData['logs'],
            'totalLogs' => $logData['total'],
            'totalPages' => $logData['totalPages'],
            'currentPage' => $page,
            'all_users' => $userModel->getAll(),
            'currentUserId' => $user_id,
            'currentFilter' => $filter,
            'currentLevel' => $level,
            'currentSearch' => $search,
            'currentPageSize' => $pageSize,
            'currentSortBy' => $sort_by,
            'currentSortOrder' => $sort_order,
        ];
    
        view('sysadmin/system_logs', $data);
    }

    public function markLogAsSeen() {
        $this->requireSysadmin();
        header('Content-Type: application/json');

        $logId = $_POST['id'] ?? null;
        if ($logId) {
            $config = require __DIR__ . '/../../config/database.php';
            $db = new Database($config);
            $logModel = new Log($db);
            $logModel->markAsSeen($logId);
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No ID provided']);
        }
        exit;
    }

    public function markAllLogsAsSeen() {
        $this->requireSysadmin();
        header('Content-Type: application/json');
        
        $config = require __DIR__ . '/../../config/database.php';
        $db = new Database($config);
        $logModel = new Log($db);
        $logModel->markAllAsSeen();

        echo json_encode(['status' => 'success']);
        exit;
    }
}