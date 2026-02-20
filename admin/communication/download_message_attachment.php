<?php
require_once '../../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$messageId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($messageId <= 0) {
    http_response_code(400);
    exit('Invalid message id.');
}

$stmt = $conn->prepare("
    SELECT attachment_original_name, attachment_stored_name, attachment_path
    FROM tutor_messages
    WHERE id = ?
    LIMIT 1
");

if (!$stmt) {
    http_response_code(500);
    exit('Unable to prepare attachment query.');
}

$stmt->bind_param('i', $messageId);
$stmt->execute();
$result = $stmt->get_result();
$message = $result ? $result->fetch_assoc() : null;
$stmt->close();

if (!$message) {
    http_response_code(404);
    exit('Message not found.');
}

$originalName = trim((string) ($message['attachment_original_name'] ?? ''));
$storedName = basename(trim((string) ($message['attachment_stored_name'] ?? '')));
$rawPath = trim((string) ($message['attachment_path'] ?? ''));

if ($originalName === '' && $storedName === '' && $rawPath === '') {
    http_response_code(404);
    exit('No attachment on this message.');
}

function normalize_path(string $path): string
{
    $path = str_replace('\\', '/', $path);
    $path = preg_replace('#/+#', '/', $path);
    return trim((string) $path);
}

function try_candidate(string $path): string
{
    $real = realpath($path);
    return ($real && is_file($real)) ? $real : '';
}

$candidates = [];
$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';
$projectRoot = realpath(__DIR__ . '/../../') ?: '';

if ($rawPath !== '' && !preg_match('/^https?:\/\//i', $rawPath)) {
    $normalized = normalize_path($rawPath);

    if (preg_match('/^[A-Za-z]:\//', $normalized) || str_starts_with($normalized, '/')) {
        $candidates[] = $normalized;
    } else {
        $normalized = ltrim($normalized, '/');
        if ($projectRoot !== '') {
            $candidates[] = $projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $normalized);
        }
        if ($documentRoot !== '') {
            $candidates[] = $documentRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $normalized);
        }
    }
}

if ($storedName !== '') {
    if ($projectRoot !== '') {
        $candidates[] = $projectRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'messages' . DIRECTORY_SEPARATOR . $storedName;
        $candidates[] = $projectRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $storedName;
    }
    if ($documentRoot !== '') {
        $candidates[] = $documentRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'messages' . DIRECTORY_SEPARATOR . $storedName;
        $candidates[] = $documentRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $storedName;
        $candidates[] = $documentRoot . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'messages' . DIRECTORY_SEPARATOR . $storedName;
        $candidates[] = $documentRoot . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $storedName;

        // Fallback: check first-level app folders under document root.
        $rootEntries = @scandir($documentRoot);
        if (is_array($rootEntries)) {
            foreach ($rootEntries as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }
                $base = $documentRoot . DIRECTORY_SEPARATOR . $entry;
                if (!is_dir($base)) {
                    continue;
                }
                $candidates[] = $base . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'messages' . DIRECTORY_SEPARATOR . $storedName;
                $candidates[] = $base . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $storedName;
            }
        }
    }
}

$filePath = '';
foreach ($candidates as $candidate) {
    $resolved = try_candidate($candidate);
    if ($resolved !== '') {
        $filePath = $resolved;
        break;
    }
}

if ($filePath === '') {
    http_response_code(404);
    exit('Attachment file not found on server.');
}

$downloadName = $originalName !== '' ? $originalName : basename($filePath);
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = $finfo ? finfo_file($finfo, $filePath) : 'application/octet-stream';
if ($finfo) {
    finfo_close($finfo);
}
if (!$mimeType) {
    $mimeType = 'application/octet-stream';
}

header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($filePath));
header('Content-Disposition: inline; filename="' . addslashes($downloadName) . '"');
header('X-Content-Type-Options: nosniff');
readfile($filePath);
exit;

