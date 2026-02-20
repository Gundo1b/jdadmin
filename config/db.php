<?php

if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_PORT')) {
    define('DB_PORT', 3306);
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', '');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'xrgconos_dash');
}

if (!defined('BASE_URL')) {
    $projectRoot = realpath(__DIR__ . '/..');
    $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    $baseUrl = '/';

    if ($projectRoot && $documentRoot) {
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $projectForCompare = $isWindows ? strtolower($projectRoot) : $projectRoot;
        $documentForCompare = $isWindows ? strtolower($documentRoot) : $documentRoot;

        if (strpos($projectForCompare, $documentForCompare) === 0) {
            $relativePath = substr($projectRoot, strlen($documentRoot));
            $relativePath = str_replace('\\', '/', $relativePath);
            $relativePath = trim($relativePath, '/');
            $baseUrl = $relativePath === '' ? '/' : '/' . $relativePath . '/';
        }
    }

    if ($baseUrl === '/') {
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $projectDir = basename(dirname(__DIR__));
        $needle = '/' . trim($projectDir, '/') . '/';
        if ($scriptName !== '' && strpos($scriptName, $needle) !== false) {
            $baseUrl = $needle;
        }
    }

    define('BASE_URL', $baseUrl);
}

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($conn->connect_error && DB_HOST === 'localhost') {
    $conn = @new mysqli('127.0.0.1', DB_USER, DB_PASS, DB_NAME, DB_PORT);
}

if ($conn->connect_error) {
    die(
        'Database Connection Failed: ' . $conn->connect_error .
        '. Check that MySQL is running in XAMPP and DB "' . DB_NAME . '" exists.'
    );
}

$conn->set_charset('utf8mb4');

?>
