<?php
require_once '../includes/Config.php';
require_once '../includes/Database.php';
require_once '../includes/MachineManager.php';
require_once '../includes/ProductionOrderManager.php';
require_once '../includes/RejectManager.php';
require_once '../includes/OperatorLogsManager.php';

header('Content-Type: application/json');

$machineId = isset($_GET['machine_id']) ? (int)$_GET['machine_id'] : 0;

if ($machineId <= 0) {
    echo json_encode(['error' => 'Invalid machine_id']);
    return;
}

try {
    $machineManager = new MachineManager($pdo);
    $poManager = new ProductionOrderManager($pdo);
    $rejectManager = new RejectManager($pdo);
    $logManager = new OperatorLogsManager($pdo);

    $machine = $machineManager->getMachineById($machineId);

    if (!$machine) {
        echo json_encode(['error' => 'Machine not found']);
        return;
    }

    $activeOrder = $poManager->getActiveOrderForMachine($machineId);
    $plannedOrders = (!$activeOrder) ? $poManager->getPlannedOrders($machineId) : [];

    $statusClass = match($machine['Status']) {
        'Active' => 'status-working',
        'Inactive' => 'status-stopped',
        'Maintenance' => 'status-maintenance',
        default => ''
    };

    $recentRejects = $rejectManager->getRecentRejects($machineId, 3);

    $operators = $logManager->getActiveOperators($machineId);

    echo json_encode([
        'machine' => $machine,
        'activeOrder' => $activeOrder,
        'plannedOrders' => $plannedOrders,
        'statusClass' => $statusClass,
        'recentRejects' => $recentRejects,
        'operators' => $operators
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
