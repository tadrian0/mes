<?php

class MockPDO extends PDO {
    public $lastPrepare;
    public $statements = [];
    public $failPrepare = false;

    public function __construct() {}

    #[\ReturnTypeWillChange]
    public function prepare(string $query, array $options = []) {
        if ($this->failPrepare) {
            throw new PDOException("Mock PDOException on prepare");
        }
        $this->lastPrepare = $query;
        $stmt = new MockPDOStatement();
        $this->statements[] = $stmt;
        return $stmt;
    }
}

class MockPDOStatement extends PDOStatement {
    public $executedParams = [];
    public $failExecute = false;

    public function __construct() {}

    #[\ReturnTypeWillChange]
    public function execute(?array $params = null) {
        if ($this->failExecute) {
            throw new PDOException("Mock PDOException on execute");
        }
        $this->executedParams = $params;
        return true;
    }
}
