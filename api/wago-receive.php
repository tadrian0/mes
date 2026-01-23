<?php
require_once '../includes/Config.php';
require_once '../includes/Database.php';
require_once '../includes/WagoManager.php';

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

$wagoManager = new WagoManager($pdo);

$machineId = $_POST['machine_id'] ?? null;
$count = $_POST['production_count'] ?? null;

if (!$machineId || !$count) {
    $input = json_decode(file_get_contents('php://input'), true);
    $machineId = $input['machine_id'] ?? null;
    $count = $input['production_count'] ?? null;
}

if ($machineId && $count) {
    if ($wagoManager->logSignal((int)$machineId, (int)$count)) {
        echo json_encode(['status' => 'success', 'message' => 'Signal received']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
}
?>