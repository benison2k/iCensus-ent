<?php
session_start();
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../core/functions.php';
require_once __DIR__ . '/../core/Database.php';

// Check for System Admin role
if ($_SESSION['user']['role_name'] !== 'System Admin') {
    $_SESSION['modal'] = ['message' => 'You do not have permission for this action.', 'type' => 'error'];
    header("Location: ../sysadmin/dashboard.php");
    exit;
}

$action = $_POST['action'] ?? '';
$config = require __DIR__ . '/../core/config.php';
$db = new Database($config);
$pdo = $db->getPdo();

// --- NEW FUNCTION: PHP-BASED BACKUP ---
function php_database_backup($pdo, $db_name) {
    // Set a reasonable execution time and memory limit
    set_time_limit(0);
    ini_set('memory_limit', '128M');

    $backupDir = __DIR__ . '/../backups/';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    $filename = $db_name . '_' . date('Y-m-d_H-i-s') . '.sql';
    $filepath = $backupDir . $filename;
    $handle = fopen($filepath, 'w');

    // Get all table names
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($tables)) {
        throw new Exception("No tables found in the database.");
    }

    $backupContent = "-- iCensus Database Backup\n-- Created: " . date('Y-m-d H:i:s') . "\n\n";
    fwrite($handle, $backupContent);
    $backupContent = "";

    foreach ($tables as $table) {
        // Drop table statement
        $stmt_drop = $pdo->query("SHOW CREATE TABLE `{$table}`");
        $create_sql = $stmt_drop->fetch(PDO::FETCH_ASSOC);
        $backupContent .= "DROP TABLE IF EXISTS `{$table}`;\n";
        $backupContent .= $create_sql['Create Table'] . ";\n\n";

        // Select all data from table
        $stmt_data = $pdo->query("SELECT * FROM `{$table}`");
        while ($row = $stmt_data->fetch(PDO::FETCH_ASSOC)) {
            $cols = array_map(function($v) use ($pdo) {
                if ($v === null) return "NULL";
                return $pdo->quote($v);
            }, $row);
            $cols = implode(', ', $cols);
            $backupContent .= "INSERT INTO `{$table}` (`" . implode("`, `", array_keys($row)) . "`) VALUES ({$cols});\n";
        }
        $backupContent .= "\n";
        
        // Write to file in chunks to save memory
        if (strlen($backupContent) > 1024 * 1024) { // 1MB chunk
            fwrite($handle, $backupContent);
            $backupContent = "";
        }
    }

    if (!empty($backupContent)) {
        fwrite($handle, $backupContent);
    }

    fclose($handle);
    return ['status' => 'success', 'filename' => $filename];
}

try {
    switch ($action) {
        case 'backup_db':
            $result = php_database_backup($pdo, $config['dbname']);
            if ($result['status'] === 'success') {
                log_action('INFO', 'DB_BACKUP', 'Database backup successful.');
                $_SESSION['modal'] = ['message' => "Database backup completed successfully. File: {$result['filename']}", 'type' => 'success'];
            }
            break;

        case 'restore_db':
            // The restore logic still relies on `mysql` shell command which may be blocked.
            // This is left as-is, but a manual restore is recommended if this fails.
            $mysql_path = "mysql";
            if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('File upload failed.');
            }
            if (pathinfo($_FILES['backup_file']['name'], PATHINFO_EXTENSION) !== 'sql') {
                throw new Exception('Invalid file type. Only .sql files are allowed.');
            }

            $tempFile = $_FILES['backup_file']['tmp_name'];
            $command = "{$mysql_path} --user=" . escapeshellarg($config['user']) . " --password=" . escapeshellarg($config['password']) . " --host=" . escapeshellarg($config['host']) . " " . escapeshellarg($config['dbname']) . " < " . escapeshellarg($tempFile) . " 2>&1";
            
            $output = [];
            $return_var = 0;
            exec($command, $output, $return_var);

            if ($return_var === 0) {
                log_action('INFO', 'DB_RESTORE', 'Database restoration successful.');
                $_SESSION['modal'] = ['message' => 'Database restored successfully.', 'type' => 'success'];
            } else {
                $error_details = implode("\n", $output);
                log_action('ERROR', 'DB_RESTORE_FAIL', 'Database restoration failed: ' . $error_details);
                $_SESSION['modal'] = ['message' => 'Database restoration failed. Check the system logs for details.', 'type' => 'error'];
            }
            break;

        default:
            $_SESSION['modal'] = ['message' => 'Invalid action.', 'type' => 'error'];
            break;
    }
} catch (Exception $e) {
    log_action('ERROR', 'DB_TOOLS_ERROR', 'An error occurred: ' . $e->getMessage());
    $_SESSION['modal'] = ['message' => 'An error occurred: ' . $e->getMessage(), 'type' => 'error'];
}

header("Location: db_tools.php");
exit;
?>