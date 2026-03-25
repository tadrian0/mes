<?php
class MockPDO extends PDO {
    public $lastQuery = null;
    public $mockStatement = null;

    public function __construct() {}

    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false {
        $this->lastQuery = $query;
        return $this->mockStatement;
    }
}

class MockPDOStatement extends PDOStatement {
    public $fetchAllResult = [];

    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array {
        return $this->fetchAllResult;
    }
}
