<?php

require_once __DIR__ . '/../includes/ArticleManager.php';

class ArticleManagerTest {

    private $pdo;
    private $manager;

    public function __construct() {
        $this->pdo = new MockPDO();
        $this->manager = new ArticleManager($this->pdo);
    }

    public function testCreateArticleSuccess() {
        $result = $this->manager->createArticle('Test Article', 'Test Description', '/path/to/image.png', 'Approved');

        assertTrue($result, "Expected createArticle to return true on success");
        // Verify we are inserting into some variable table name without hardcoding "article"
        assertStringContainsString('INSERT INTO ', $this->pdo->preparedQuery, "Expected query to contain INSERT INTO");
        assertStringContainsString('(Name, Description, ImagePath, QualityControl)', $this->pdo->preparedQuery, "Expected query to contain correct columns");
        assertEquals(['Test Article', 'Test Description', '/path/to/image.png', 'Approved'], $this->pdo->statement->executeArgs, "Expected arguments to match");
    }

    public function testCreateArticleEmptyNameFails() {
        $result = $this->manager->createArticle('');

        assertFalse($result, "Expected createArticle to return false when name is empty");
    }

    public function testCreateArticleDatabaseExceptionFails() {
        // Simulate a PDOException during execute
        $this->pdo->statement->shouldThrow = true;

        $result = $this->manager->createArticle('Another Article');

        assertFalse($result, "Expected createArticle to return false on PDOException");
    }
}
