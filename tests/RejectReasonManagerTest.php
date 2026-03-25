<?php

require_once __DIR__ . '/../includes/RejectReasonManager.php';

class RejectReasonManagerTest {

    public function testUpdateSuccess() {
        $mockStmt = $this->createMockPDOStatement(true);
        $mockPdo = $this->createMockPDO($mockStmt);

        $manager = new RejectReasonManager($mockPdo);

        // signature: update(int $id, string $name, int $categoryId, ?int $plantId, ?int $sectionId)
        $result = $manager->update(1, 'Reason 1', 2, 3, 4);

        if ($result !== true) {
            throw new Exception("Expected true on success, got false.");
        }

        // Check if the correct parameters were bound
        // The query is: UPDATE reject_reason SET ReasonName=?, CategoryID=?, PlantID=?, SectionID=? WHERE ReasonID=?
        // The execute call in update: return $stmt->execute([$name, $categoryId, $plantId ?: null, $sectionId ?: null, $id]);
        $expectedParams = ['Reason 1', 2, 3, 4, 1];
        if ($mockStmt->lastExecuteParams !== $expectedParams) {
             throw new Exception("Parameters mismatch. Expected " . json_encode($expectedParams) . ", got " . json_encode($mockStmt->lastExecuteParams));
        }
    }

    public function testUpdateWithNulls() {
        $mockStmt = $this->createMockPDOStatement(true);
        $mockPdo = $this->createMockPDO($mockStmt);

        $manager = new RejectReasonManager($mockPdo);

        $result = $manager->update(1, 'Reason 1', 2, null, null);

        if ($result !== true) {
            throw new Exception("Expected true on success, got false.");
        }

        $expectedParams = ['Reason 1', 2, null, null, 1];
        if ($mockStmt->lastExecuteParams !== $expectedParams) {
             throw new Exception("Parameters mismatch. Expected " . json_encode($expectedParams) . ", got " . json_encode($mockStmt->lastExecuteParams));
        }
    }

    public function testUpdateWithZeros() {
        $mockStmt = $this->createMockPDOStatement(true);
        $mockPdo = $this->createMockPDO($mockStmt);

        $manager = new RejectReasonManager($mockPdo);

        // $plantId ?: null evaluates to null if $plantId is 0, because 0 is falsy
        // The execute call in update: return $stmt->execute([$name, $categoryId, $plantId ?: null, $sectionId ?: null, $id]);
        $result = $manager->update(1, 'Reason 1', 2, 0, 0);

        if ($result !== true) {
            throw new Exception("Expected true on success, got false.");
        }

        $expectedParams = ['Reason 1', 2, null, null, 1];
        if ($mockStmt->lastExecuteParams !== $expectedParams) {
             throw new Exception("Parameters mismatch. Expected " . json_encode($expectedParams) . ", got " . json_encode($mockStmt->lastExecuteParams));
        }
    }

    public function testUpdateException() {
        // Create a mock statement that throws an exception on execute
        $reflection = new ReflectionClass('MockPDOStatement');
        $mockStmt = $reflection->newInstanceWithoutConstructor();

        $throwingStmt = new class($mockStmt) extends MockPDOStatement {
            public function __construct($original) {}
            public function execute(?array $params = null): bool {
                throw new PDOException("Simulated error");
            }
        };

        $mockPdo = $this->createMockPDO($throwingStmt);

        $manager = new RejectReasonManager($mockPdo);
        $result = $manager->update(1, 'Reason 1', 2, 3, 4);

        if ($result !== false) {
             throw new Exception("Expected false on exception, got " . var_export($result, true));
        }
    }

    private function createMockPDOStatement(bool $executeReturn) {
        $reflection = new ReflectionClass('MockPDOStatement');
        $mockStmt = $reflection->newInstanceWithoutConstructor();

        return new class($mockStmt, $executeReturn) extends MockPDOStatement {
            private $executeReturn;
            public $lastExecuteParams = [];

            public function __construct($original, $executeReturn) {
                $this->executeReturn = $executeReturn;
            }

            public function execute(?array $params = null): bool {
                $this->lastExecuteParams = $params;
                return $this->executeReturn;
            }
        };
    }

    private function createMockPDO($stmt) {
        return new class($stmt) extends MockPDO {
            private $stmt;
            public function __construct($stmt) {
                $this->stmt = $stmt;
            }
            public function prepare(string $query, array $options = []): PDOStatement|false {
                return $this->stmt;
            }
        };
    }
}
