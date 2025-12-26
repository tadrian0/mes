<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/mes/includes/Config.php';
require_once INCLUDE_PATH . 'Config.php';
require_once INCLUDE_PATH . 'Database.php';
require_once INCLUDE_PATH . 'MachineManager.php';
//require_once INCLUDE_PATH . 'ApiAuth.php';

header('Content-Type: application/json');

$machineManager = new MachineManager($pdo);
$data = $machineManager->listMachines();

echo json_encode(['data' => $data]);
?>