<?php
class ArticleManager
{
    private $pdo;
    private $tableName = "article";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createArticle(string $name, ?string $description = null, ?string $imagePath = null, string $qualityControl = 'Pending'): bool
    {
        if (empty($name)) {
            return false;
        }
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO $this->tableName (Name, Description, ImagePath, QualityControl)
                VALUES (?, ?, ?, ?)
            ");
            return $stmt->execute([$name, $description, $imagePath, $qualityControl]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getArticleById(int $articleId): ?array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT ArticleID, Name, Description, ImagePath, QualityControl, CreatedAt, UpdatedAt
                FROM $this->tableName
                WHERE ArticleID = ?
            ");
            $stmt->execute([$articleId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function updateArticle(int $articleId, ?string $name = null, ?string $description = null, ?string $imagePath = null, ?string $qualityControl = null): bool
    {
        $updates = [];
        $params = [];
        if ($name !== null && $name !== '') {
            $updates[] = 'Name = ?';
            $params[] = $name;
        }
        if ($description !== null) {
            $updates[] = 'Description = ?';
            $params[] = $description;
        }
        if ($imagePath !== null) {
            $updates[] = 'ImagePath = ?';
            $params[] = $imagePath;
        }
        if ($qualityControl !== null && in_array($qualityControl, ['Pending', 'Approved', 'Rejected'])) {
            $updates[] = 'QualityControl = ?';
            $params[] = $qualityControl;
        }
        if (empty($updates)) {
            return false;
        }
        $params[] = $articleId;
        try {
            $stmt = $this->pdo->prepare("
                UPDATE $this->tableName
                SET " . implode(', ', $updates) . "
                WHERE ArticleID = ?
            ");
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteArticle(int $articleId): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM $this->tableName WHERE ArticleID = ?");
            return $stmt->execute([$articleId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function listArticles(): array
    {
        try {
            $stmt = $this->pdo->query("
                SELECT ArticleID, Name, Description, ImagePath, QualityControl, CreatedAt, UpdatedAt
                FROM $this->tableName
                ORDER BY Name ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>