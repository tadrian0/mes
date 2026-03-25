<?php

/**
 * Mock PDO class for testing.
 * Bypasses actual database connection.
 */
class MockPDO extends PDO
{
    public $prepareResult;

    public function __construct()
    {
        // Prevent actual connection attempts
    }

    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        if ($this->prepareResult instanceof PDOStatement) {
            // Give the statement access to the query if needed
            $this->prepareResult->queryString = $query;
            return $this->prepareResult;
        }

        if ($this->prepareResult === false) {
            return false;
        }

        if ($this->prepareResult instanceof Throwable) {
            throw $this->prepareResult;
        }

        // Fallback: Return a basic mock statement
        return (new ReflectionClass(MockPDOStatement::class))->newInstanceWithoutConstructor();
    }

    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false
    {
        if ($this->prepareResult instanceof PDOStatement) {
            $this->prepareResult->queryString = $query;
            return $this->prepareResult;
        }

        if ($this->prepareResult instanceof Throwable) {
            throw $this->prepareResult;
        }

        return (new ReflectionClass(MockPDOStatement::class))->newInstanceWithoutConstructor();
    }
}

/**
 * Mock PDOStatement class.
 * Allows injecting pre-determined results for execution and fetching.
 */
class MockPDOStatement extends PDOStatement
{
    public $executeResult = true;
    public $fetchResult = false;
    public $fetchAllResult = [];
    public $executedParams = [];
    public $executeException = null;

    public function __construct()
    {
    }

    public function execute(?array $params = null): bool
    {
        $this->executedParams = $params;

        if ($this->executeException !== null) {
            throw $this->executeException;
        }

        return $this->executeResult;
    }

    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $cursorOrientation = PDO::FETCH_ORI_NEXT, int $cursorOffset = 0): mixed
    {
        return $this->fetchResult;
    }

    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        return $this->fetchAllResult;
    }
}
