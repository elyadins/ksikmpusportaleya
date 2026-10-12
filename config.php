<?php
/**
 * NEXUS CAMPUS PORTAL
 * Database Configuration (Support Local XAMPP & Railway Cloud Deployment)
 */

// Otomatis baca env dari Railway atau MySQL Cloud lainnya
$dbHost = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'localhost';
$dbPort = getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: 3306;
$dbUser = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root';
$dbPass = getenv('MYSQLPASSWORD') ?: (getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
$dbName = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'nexus_campus';

define('DB_HOST',   $dbHost);
define('DB_USER',   $dbUser);
define('DB_PASS',   $dbPass);
define('DB_NAME',   $dbName);
define('DB_PORT',   (int)$dbPort);
define('DB_CHARSET','utf8mb4');

// App Config
define('APP_NAME',    'NexusCampus');
define('APP_VERSION', '2.0.1');

// Auto-detect APP_URL robustly
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';

// Jika di Railway atau port 8000 (root), tidak perlu subfolder /kampus-portal
if (strpos($host, 'localhost:8000') !== false || strpos($host, 'railway.app') !== false || strpos($host, 'up.railway.app') !== false) {
    define('APP_URL', rtrim($protocol . $host, '/'));
} else {
    // Local XAMPP default
    define('APP_URL', rtrim($protocol . $host, '/') . '/kampus-portal');
}

define('UPLOAD_DIR',  __DIR__ . '/../uploads/tugas/');
define('MAX_FILE_MB', 10);  // max upload 10MB

function getDB(): ?PDO {
    static $pdo = null;
    static $connectionAttempted = false;

    if ($pdo === null && !$connectionAttempted) {
        $connectionAttempted = true;
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_TIMEOUT            => 3,
            ]);
        } catch (PDOException $e) {
            // Jangan crash 500 fatal jika DB belum aktif, log dan kembalikan null atau mock aman
            error_log('Database connection notice: ' . $e->getMessage());
            $pdo = null;
        }
    }
    return $pdo;
}
