<?php
require_once __DIR__ . '/../includes/WagoManager.php';

class WagoManagerTest {
    public function testLogSignalSuccess() {
        $pdo = new MockPDO();
        $stmt = new MockPDOStatement();
        $pdo->prepareReturn = $stmt;

        $manager = new WagoManager($pdo);

        $result = $manager->logSignal(1, 100);

        Assert::assertTrue($result);
        Assert::assertStringContainsString('INSERT INTO wago (MachineID, ProductionCount, Timestamp, Processed)', $pdo->lastQuery);
        Assert::assertSame([1, 100], $stmt->lastParams);
    }

    public function testLogSignalPdoException() {
        $pdo = new MockPDO();
        $pdo->prepareException = new PDOException("Database error");

        $manager = new WagoManager($pdo);

        $result = $manager->logSignal(1, 100);

        Assert::assertFalse($result);
    }
}
