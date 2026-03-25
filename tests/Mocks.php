<?php
class MockPDO extends PDO {
    public function __construct() {}

    private $statement;
    private $queryResult;
    private $prepareResult;
    private $lastQuery;

    public function setMockStatement($stmt) {
        $this->statement = $stmt;
    }

    public function setQueryResult($result) {
        $this->queryResult = $result;
    }

    public function setPrepareResult($result) {
        $this->prepareResult = $result;
    }

    public function query($statement, $mode = PDO::FETCH_DEFAULT, ...$fetch_mode_args) {
        $this->lastQuery = $statement;
        if ($this->queryResult !== null) {
            return $this->queryResult;
        }
        return false;
    }

    public function prepare($query, $options = []) {
        $this->lastQuery = $query;
        if ($this->prepareResult !== null) {
            return $this->prepareResult;
        }
        return false;
    }

    public function getLastQuery() {
        return $this->lastQuery;
    }
}

class MockPDOStatement extends PDOStatement {
    private $fetchAllResult;
    private $executeResult = true;
    private $lastParams = [];

    public function __construct() {}

    public function setFetchAllResult($result) {
        $this->fetchAllResult = $result;
    }

    public function setExecuteResult($result) {
        $this->executeResult = $result;
    }

    public function execute($params = null) {
        $this->lastParams = $params;
        return $this->executeResult;
    }

    public function fetchAll($mode = PDO::FETCH_DEFAULT, ...$args) {
        return $this->fetchAllResult;
    }

    public function getLastParams() {
        return $this->lastParams;
    }
}
