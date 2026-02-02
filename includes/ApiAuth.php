<?php
require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/ApiKeyManager.php';

$apiKey = null;

// Helper to get headers if apache_request_headers is missing
function get_request_headers() {
    $headers = [];
    foreach ($_SERVER as $key => $value) {
        if (substr($key, 0, 5) <> 'HTTP_') {
            continue;
        }
        $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
        $headers[$header] = $value;
    }
    return $headers;
}

$headers = function_exists('apache_request_headers') ? apache_request_headers() : get_request_headers();

// Normalize headers key
$headers = array_change_key_case($headers, CASE_UPPER);

if (isset($headers['X-API-KEY'])) {
    $apiKey = $headers['X-API-KEY'];
} elseif (isset($headers['AUTHORIZATION'])) {
    $matches = [];
    if (preg_match('/Bearer\s(\S+)/', $headers['AUTHORIZATION'], $matches)) {
        $apiKey = $matches[1];
    }
}

// Also check $_SERVER directly as fallback
if (!$apiKey && isset($_SERVER['HTTP_X_API_KEY'])) {
    $apiKey = $_SERVER['HTTP_X_API_KEY'];
}

if (!$apiKey) {
    http_response_code(401);
    echo json_encode(['error' => 'Missing API Key. Please provide X-API-KEY header.']);
    exit;
}

// $pdo comes from Database.php
$apiManager = new ApiKeyManager($pdo);
$endpoint = basename($_SERVER['PHP_SELF']);

if (!$apiManager->validateAndLog($apiKey, $endpoint)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid or inactive API Key.']);
    exit;
}
?>