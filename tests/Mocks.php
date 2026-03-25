<?php

class MockPDOStatement {
    public $executeResult = true;
    public $executeArgs = [];
    public $shouldThrow = false;
    public $exceptionMessage = "Mocked PDOException";

    public function execute($args = []) {
        $this->executeArgs = $args;
        if ($this->shouldThrow) {
            throw new PDOException($this->exceptionMessage);
        }
        return $this->executeResult;
    }

    public function fetch($mode = PDO::FETCH_ASSOC) {
        return [];
    }

    public function fetchAll($mode = PDO::FETCH_ASSOC) {
        return [];
    }
}

class MockPDO extends PDO {
    public $statement;
    public $preparedQuery = "";
    public $shouldThrow = false;
    public $exceptionMessage = "Mocked PDOException in prepare";

    public function __construct() {
        $this->statement = new MockPDOStatement();
    }

    public function prepare($query, $options = []) {
        $this->preparedQuery = $query;
        if ($this->shouldThrow) {
            throw new PDOException($this->exceptionMessage);
        }
        return $this->statement;
    }

    public function query($query, $fetchMode = null, ...$fetchModeArgs) {
        return $this->statement;
    }
}

function assertEquals($expected, $actual, $message = '') {
    if ($expected !== $actual) {
        throw new Exception("Assertion failed: " . ($message ? $message : "Expected " . var_export($expected, true) . ", got " . var_export($actual, true)));
    }
}

function assertTrue($actual, $message = '') {
    assertEquals(true, $actual, $message);
}

function assertFalse($actual, $message = '') {
    assertEquals(false, $actual, $message);
}

function assertStringContainsString($needle, $haystack, $message = '') {
    if (strpos($haystack, $needle) === false) {
        throw new Exception("Assertion failed: " . ($message ? $message : "Expected string '$haystack' to contain '$needle'"));
    }
}
