<?php

// A helper file to define test mocks for PDO and PDOStatement.
// In the PHP backend test suite, ReflectionClass::newInstanceWithoutConstructor()
// is used to instantiate these mock objects without calling the PDO constructor.

class MockPDO extends PDO {
    // Override __construct so we don't attempt to connect to a real database
    public function __construct() {}
}

class MockPDOStatement extends PDOStatement {
    // Override __construct
    public function __construct() {}
}
