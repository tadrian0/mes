<?php
require_once __DIR__ . '/../includes/ArticleManager.php';
require_once __DIR__ . '/Mocks.php';

class ArticleManagerTest {
    public function testDeleteArticleSuccess() {
        $pdo = new ReflectionClass('MockPDO');
        $mockPdo = $pdo->newInstanceWithoutConstructor();

        $stmt = new ReflectionClass('MockPDOStatement');
        $mockStmt = $stmt->newInstanceWithoutConstructor();

        $mockStmt->setExecuteResult(true);
        $mockPdo->setPrepareResult($mockStmt);

        $manager = new ArticleManager($mockPdo);
        $result = $manager->deleteArticle(123);

        if ($result !== true) {
            throw new Exception("Expected true, got " . var_export($result, true));
        }

        if ($mockStmt->getLastParams() !== [123]) {
            throw new Exception("Expected param [123], got " . var_export($mockStmt->getLastParams(), true));
        }
    }

    public function testDeleteArticleFailure() {
        $pdo = new ReflectionClass('MockPDO');
        $mockPdo = $pdo->newInstanceWithoutConstructor();

        $stmt = new ReflectionClass('MockPDOStatement');
        $mockStmt = $stmt->newInstanceWithoutConstructor();

        $mockStmt->setExecuteResult(false);
        $mockPdo->setPrepareResult($mockStmt);

        $manager = new ArticleManager($mockPdo);
        $result = $manager->deleteArticle(456);

        if ($result !== false) {
            throw new Exception("Expected false, got " . var_export($result, true));
        }
    }

    public function testDeleteArticleException() {
        // We need to create a mock PDO that throws a PDOException on prepare
        $mockPdo = new class extends PDO {
            public function __construct() {}
            public function prepare(string $query, array $options = []): PDOStatement|false {
                throw new PDOException("Test exception");
            }
        };

        $manager = new ArticleManager($mockPdo);
        $result = $manager->deleteArticle(789);

        if ($result !== false) {
            throw new Exception("Expected false on exception, got " . var_export($result, true));
        }
    }
}
