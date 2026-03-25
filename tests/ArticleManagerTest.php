<?php

require_once __DIR__ . '/../includes/ArticleManager.php';

class ArticleManagerTest {
    public function testListArticlesSuccess() {
        $mockStmt = $this->createMockPDOStatement();
        $expectedData = [
            [
                'ArticleID' => 1,
                'Name' => 'Test Article A',
                'Description' => 'Description A',
                'ImagePath' => null,
                'QualityControl' => 'Pending',
                'CreatedAt' => '2023-01-01 12:00:00',
                'UpdatedAt' => '2023-01-01 12:00:00',
            ],
            [
                'ArticleID' => 2,
                'Name' => 'Test Article B',
                'Description' => 'Description B',
                'ImagePath' => '/path/to/image.jpg',
                'QualityControl' => 'Approved',
                'CreatedAt' => '2023-01-02 12:00:00',
                'UpdatedAt' => '2023-01-02 12:00:00',
            ]
        ];

        $mockStmt->data = $expectedData;

        // Use an anonymous class extending MockPDO
        $mockPdo = new class($mockStmt) extends MockPDO {
            private $stmt;
            public function __construct($stmt) {
                $this->stmt = $stmt;
            }
            public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false {
                // Verify the query being executed
                if (strpos($query, 'SELECT ArticleID, Name, Description, ImagePath, QualityControl, CreatedAt, UpdatedAt') !== false) {
                    return $this->stmt;
                }
                return false;
            }
        };

        $manager = new ArticleManager($mockPdo);
        $result = $manager->listArticles();

        if ($result !== $expectedData) {
            throw new Exception("listArticles() returned unexpected data.");
        }
    }

    public function testListArticlesFailure() {
        // Mock PDO throwing a PDOException
        $mockPdo = new class extends MockPDO {
            public function __construct() {}
            public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false {
                throw new PDOException("Simulated database failure.");
            }
        };

        $manager = new ArticleManager($mockPdo);
        $result = $manager->listArticles();

        if ($result !== []) {
            throw new Exception("Expected an empty array on exception, got something else.");
        }
    }

    private function createMockPDOStatement() {
        $reflection = new ReflectionClass('MockPDOStatement');
        $mockStmt = $reflection->newInstanceWithoutConstructor();

        // We use an anonymous class extending PDOStatement to hold state for mocking fetchAll
        return new class($mockStmt) extends MockPDOStatement {
            public $data = [];

            public function __construct($original) {
            }

            public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array {
                // Ignore the mode and just return the hardcoded data
                return $this->data;
            }
        };
    }
}
