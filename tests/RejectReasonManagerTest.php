<?php
require_once __DIR__ . '/Mocks.php';
require_once __DIR__ . '/../includes/RejectReasonManager.php';

function testGetCategoriesReturnsCategories() {
    $pdo = new MockPDO();

    // Use Reflection to instantiate MockPDOStatement safely
    $reflection = new ReflectionClass('MockPDOStatement');
    $stmt = $reflection->newInstanceWithoutConstructor();
    $stmt->fetchAllResult = [
        ['CategoryID' => 1, 'CategoryName' => 'Mechanical'],
        ['CategoryID' => 2, 'CategoryName' => 'Electrical']
    ];

    $pdo->mockStatement = $stmt;

    $manager = new RejectReasonManager($pdo);
    $result = $manager->getCategories();

    if ($result !== $stmt->fetchAllResult) {
        echo "FAIL: Expected categories do not match result.\n";
        return false;
    }

    $expectedQuery = "SELECT * FROM reject_category ORDER BY CategoryName";
    if ($pdo->lastQuery !== $expectedQuery) {
        echo "FAIL: Unexpected query executed: {$pdo->lastQuery}\n";
        return false;
    }

    return true;
}

function testGetCategoriesHandlesEmptyResult() {
    $pdo = new MockPDO();

    $reflection = new ReflectionClass('MockPDOStatement');
    $stmt = $reflection->newInstanceWithoutConstructor();
    $stmt->fetchAllResult = [];

    $pdo->mockStatement = $stmt;

    $manager = new RejectReasonManager($pdo);
    $result = $manager->getCategories();

    if ($result !== []) {
        echo "FAIL: Expected empty array, got something else.\n";
        return false;
    }

    return true;
}
