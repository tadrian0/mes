<?php

require_once __DIR__ . '/Mocks.php';
require_once __DIR__ . '/../includes/ArticleManager.php';

class ArticleManagerTest
{
    public function run()
    {
        $this->testGetArticleByIdSuccess();
        $this->testGetArticleByIdNotFound();
        $this->testGetArticleByIdException();
        echo "ArticleManagerTest: All tests passed.\n";
    }

    private function testGetArticleByIdSuccess()
    {
        // Arrange
        $pdo = new MockPDO();
        $stmt = (new ReflectionClass(MockPDOStatement::class))->newInstanceWithoutConstructor();

        // Define successful output
        $expectedData = [
            'ArticleID' => 123,
            'Name' => 'Test Article',
            'Description' => 'Test Description',
            'ImagePath' => null,
            'QualityControl' => 'Pending',
            'CreatedAt' => '2023-01-01 00:00:00',
            'UpdatedAt' => '2023-01-01 00:00:00'
        ];

        $stmt->fetchResult = $expectedData;
        $pdo->prepareResult = $stmt;

        $manager = new ArticleManager($pdo);

        // Act
        $result = $manager->getArticleById(123);

        // Assert
        if ($result !== $expectedData) {
            throw new Exception("testGetArticleByIdSuccess failed: Expected " . print_r($expectedData, true) . ", got " . print_r($result, true));
        }

        if ($stmt->executedParams !== [123]) {
            throw new Exception("testGetArticleByIdSuccess failed: Expected params [123], got " . print_r($stmt->executedParams, true));
        }
    }

    private function testGetArticleByIdNotFound()
    {
        // Arrange
        $pdo = new MockPDO();
        $stmt = (new ReflectionClass(MockPDOStatement::class))->newInstanceWithoutConstructor();

        // Return false to simulate no records found
        $stmt->fetchResult = false;
        $pdo->prepareResult = $stmt;

        $manager = new ArticleManager($pdo);

        // Act
        $result = $manager->getArticleById(999);

        // Assert
        if ($result !== null) {
            throw new Exception("testGetArticleByIdNotFound failed: Expected null, got " . print_r($result, true));
        }
    }

    private function testGetArticleByIdException()
    {
        // Arrange
        $pdo = new MockPDO();
        $stmt = (new ReflectionClass(MockPDOStatement::class))->newInstanceWithoutConstructor();

        // Simulate an exception thrown on execution
        $stmt->executeException = new PDOException("Database connection failed");
        $pdo->prepareResult = $stmt;

        $manager = new ArticleManager($pdo);

        // Act
        $result = $manager->getArticleById(123);

        // Assert
        if ($result !== null) {
            throw new Exception("testGetArticleByIdException failed: Expected null, got " . print_r($result, true));
        }
    }
}
