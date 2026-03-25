<?php
require_once __DIR__ . '/../includes/RejectReasonManager.php';
require_once __DIR__ . '/Mocks.php';

class RejectReasonManagerTest {
    public function testDeleteSuccess() {
        $pdo = new MockPDO();
        $manager = new RejectReasonManager($pdo);

        $result = $manager->delete(5);

        if ($result !== true) {
            throw new Exception("Expected delete to return true");
        }

        if ($pdo->lastPrepare !== "DELETE FROM reject_reason WHERE ReasonID=?") {
            throw new Exception("Unexpected query: " . $pdo->lastPrepare);
        }

        $stmt = $pdo->statements[0];
        if ($stmt->executedParams !== [5]) {
            throw new Exception("Expected parameters [5], got " . json_encode($stmt->executedParams));
        }
    }

    public function testDeletePrepareException() {
        $pdo = new MockPDO();
        $pdo->failPrepare = true;
        $manager = new RejectReasonManager($pdo);

        $result = $manager->delete(5);

        if ($result !== false) {
            throw new Exception("Expected delete to return false on prepare exception");
        }
    }

    public function testDeleteExecuteException() {
        $pdo = new class extends MockPDO {
            #[\ReturnTypeWillChange]
            public function prepare(string $query, array $options = []) {
                $this->lastPrepare = $query;
                $stmt = new MockPDOStatement();
                $stmt->failExecute = true;
                $this->statements[] = $stmt;
                return $stmt;
            }
        };
        $manager = new RejectReasonManager($pdo);

        $result = $manager->delete(5);

        if ($result !== false) {
            throw new Exception("Expected delete to return false on execute exception");
        }
    }
}
