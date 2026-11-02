<?php
// AtharEye - عين الأثر
// Uses the original database name while keeping AtharEye tables isolated from the legacy schema.
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tabuk_heritage');
define('BASE_URL', '');
define('ATHAREYE_SCHEMA_VERSION', '16');

define('APP_NAME_AR', 'عين الأثر');
define('APP_NAME_EN', 'AtharEye');

if (function_exists('mysqli_report')) { mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); }

function athareye_base_path(): string {
    $path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    if (preg_match('#/(admin|user|archaeologist)$#', $path)) {
        $path = str_replace('\\', '/', dirname($path));
    }
    return rtrim($path === '/' ? '' : $path, '/');
}

function redirect_to_installer(string $reason = 'schema'): never {
    if (PHP_SAPI === 'cli') {
        throw new RuntimeException('AtharEye database installation is required: ' . $reason);
    }
    $target = athareye_base_path() . '/install.php?need_install=1&reason=' . rawurlencode($reason);
    header('Location: ' . $target);
    exit;
}

function athareye_schema_is_ready(mysqli $conn): bool {
    try {
        $table = $conn->query("SHOW TABLES LIKE 'ae_schema_meta'");
        if (!$table || $table->num_rows === 0) return false;
        $result = $conn->query("SELECT schema_version FROM ae_schema_meta WHERE id=1 LIMIT 1");
        $row = $result ? $result->fetch_assoc() : null;
        if (!$row || (string)$row['schema_version'] !== (string)ATHAREYE_SCHEMA_VERSION) return false;
        $required = [
            'ae_users' => ['id','name','email','password_hash','role','status','phone','preferred_language'],
            'ae_heritage_sites' => ['id','name','city','region','latitude','longitude','risk_percentage','risk_level','status'],
            'ae_incident_reports' => ['id','site_id','status'],
            'ae_field_assessments' => ['id','site_id','risk_percentage','status'],
        ];
        foreach ($required as $tableName => $columns) {
            $check = $conn->query("SHOW TABLES LIKE '" . $conn->real_escape_string($tableName) . "'");
            if (!$check || $check->num_rows === 0) return false;
            foreach ($columns as $column) {
                $columnEsc = $conn->real_escape_string($column);
                $col = $conn->query("SHOW COLUMNS FROM `{$tableName}` LIKE '{$columnEsc}'");
                if (!$col || $col->num_rows === 0) return false;
            }
        }
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function db(bool $withoutDatabase = false): mysqli {
    static $conn = null;
    static $serverConn = null;
    static $schemaChecked = false;

    if ($withoutDatabase) {
        if (!$serverConn) {
            $serverConn = new mysqli(DB_HOST, DB_USER, DB_PASS);
            $serverConn->set_charset('utf8mb4');
        }
        return $serverConn;
    }

    if (!$conn) {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            $conn->set_charset('utf8mb4');
        } catch (mysqli_sql_exception $e) {
            if (!defined('ATHAREYE_INSTALLING')) redirect_to_installer('database');
            throw $e;
        }
    }

    if (!$schemaChecked && !defined('ATHAREYE_INSTALLING')) {
        $schemaChecked = true;
        if (!athareye_schema_is_ready($conn)) redirect_to_installer('schema');
    }

    return $conn;
}
