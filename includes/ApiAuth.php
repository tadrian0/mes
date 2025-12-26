<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/mes/includes/Config.php';
require_once INCLUDE_PATH . 'Database.php';
require_once INCLUDE_PATH . 'ApiKeyManager.php';

$headers = apache_request_headers();
$apiKey = null;

if (isset($headers['X-API-KEY'])) {
    $apiKey = $headers['X-API-KEY'];
} elseif (isset($headers['Authorization'])) {
    $matches = [];
    if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
        $apiKey = $matches[1];
    }
}

if (!$apiKey) {
    http_response_code(401);
    echo json_encode(['error' => 'Missing API Key. Please provide X-API-KEY header.']);
    exit;
}

$apiManager = new ApiKeyManager($pdo);
$endpoint = basename($_SERVER['PHP_SELF']);

if (!$apiManager->validateAndLog($apiKey, $endpoint)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid or inactive API Key.']);
    exit;
}
?>