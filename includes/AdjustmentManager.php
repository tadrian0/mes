<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/mes/includes/Config.php';
require_once INCLUDE_PATH . 'ArticleManager.php'; 

class AdjustmentManager
{
    private $pdo;
    private $tableName = "adjustment";
    private $articleManager;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->articleManager = new ArticleManager($pdo);
    }

    public function createAdjustment(int $productionOrderId, int $articleId, int $quantity): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO $this->tableName (ProductionOrderId, ArticleId, Quantity)
                VALUES (?, ?, ?)
            ");
            return $stmt->execute([$productionOrderId, $articleId, $quantity]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getAdjustmentById(int $adjustmentId): ?array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT AdjustmentID, ProductionOrderId, ArticleId, Quantity
                FROM $this->tableName
                WHERE AdjustmentID = ?
            ");
            $stmt->execute([$adjustmentId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function updateAdjustment(int $adjustmentId, ?int $productionOrderId = null, ?int $articleId = null, ?int $quantity = null): bool
    {
        $updates = [];
        $params = [];

        if ($productionOrderId !== null) {
            $updates[] = 'ProductionOrderId = ?';
            $params[] = $productionOrderId;
        }
        if ($articleId !== null) {
            $updates[] = 'ArticleId = ?';
            $params[] = $articleId;
        }
        if ($quantity !== null) {
            $updates[] = 'Quantity = ?';
            $params[] = $quantity;
        }
        if (empty($updates)) {
            return false;
        }

        $params[] = $adjustmentId;

        try {
            $stmt = $this->pdo->prepare("
                UPDATE $this->tableName
                SET " . implode(', ', $updates) . "
                WHERE AdjustmentID = ?
            ");
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteAdjustment(int $adjustmentId): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM $this->tableName WHERE AdjustmentID = ?");
            return $stmt->execute([$adjustmentId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function listAdjustments(): array
    {
        try {
            $stmt = $this->pdo->query("
                SELECT AdjustmentID, ProductionOrderId, ArticleId, Quantity
                FROM $this->tableName
                ORDER BY AdjustmentID ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>