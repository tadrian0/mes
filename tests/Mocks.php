<?php

class MockPDO extends PDO {
    public $prepareReturn;
    public $prepareException;
    public $lastQuery;

    public function __construct() {}

    #[\ReturnTypeWillChange]
    public function prepare(string $query, array $options = []): PDOStatement|false {
        if ($this->prepareException) {
            throw $this->prepareException;
        }
        $this->lastQuery = $query;
        return $this->prepareReturn;
    }
}

class MockPDOStatement extends PDOStatement {
    public $executeReturn = true;
    public $executeException;
    public $lastParams = [];

    public function __construct() {}

    #[\ReturnTypeWillChange]
    public function execute(?array $params = null): bool {
        if ($this->executeException) {
            throw $this->executeException;
        }
        $this->lastParams = $params;
        return $this->executeReturn;
    }
}
