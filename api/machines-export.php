<?php
require_once '../includes/Config.php';
require_once '../includes/Database.php';
require_once '../includes/MachineManager.php';

header("Access-Control-Allow-Origin: *");

$format = $_GET['format'] ?? 'json'; 
$machineManager = new MachineManager($pdo);
$machines = $machineManager->listMachines();

if ($format === 'csv') {
    header('Content-Type: text/plain');
    $ids = array_column($machines, 'MachineID');
    echo implode(',', $ids);
} else {
    header('Content-Type: application/json');
    // Return minimal data for simulation
    $cleanList = array_map(function($m) {
        return ['id' => $m['MachineID'], 'name' => $m['Name']];
    }, $machines);
    echo json_encode($cleanList);
}
?>