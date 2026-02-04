<?php
class RejectManager
{
    private $pdo;
    private $tableName = "reject";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createReject(int $orderId, int $articleId, int $operatorId, int $machineId, int $categoryId, int $reasonId, int $quantity, ?string $rejectDate = null, ?string $notes = null): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO $this->tableName (OrderID, ArticleID, OperatorID, MachineID, CategoryID, ReasonID, Quantity, RejectDate, Notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $rejectDate = empty($rejectDate) ? date('Y-m-d H:i:s') : $rejectDate;

            return $stmt->execute([$orderId, $articleId, $operatorId, $machineId, $categoryId, $reasonId, $quantity, $rejectDate, $notes]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateReject(int $rejectId, int $orderId, int $articleId, int $operatorId, int $machineId, int $categoryId, int $reasonId, int $quantity, ?string $rejectDate = null, ?string $notes = null): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE $this->tableName 
                SET OrderID = ?, ArticleID = ?, OperatorID = ?, MachineID = ?, CategoryID = ?, ReasonID = ?, Quantity = ?, RejectDate = ?, Notes = ?
                WHERE RejectID = ?
            ");

            $rejectDate = empty($rejectDate) ? date('Y-m-d H:i:s') : $rejectDate;

            return $stmt->execute([$orderId, $articleId, $operatorId, $machineId, $categoryId, $reasonId, $quantity, $rejectDate, $notes, $rejectId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteReject(int $rejectId): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM $this->tableName WHERE RejectID = ?");
            return $stmt->execute([$rejectId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function listRejects(?int $filterOrder = null, ?int $filterArticle = null, ?int $filterOperator = null, ?int $filterCategory = null, ?int $filterReason = null, ?string $startDate = null, ?string $endDate = null): array
    {
        try {
            $sql = "SELECT 
                        r.*,
                        po.OrderID,
                        a.Name AS ArticleName,
                        u.OperatorUsername,
                        m.Name AS MachineName,
                        rc.CategoryName,
                        rr.ReasonName
                    FROM $this->tableName r
                    LEFT JOIN production_order po ON r.OrderID = po.OrderID
                    LEFT JOIN article a ON r.ArticleID = a.ArticleID
                    LEFT JOIN user u ON r.OperatorID = u.OperatorID
                    LEFT JOIN machine m ON r.MachineID = m.MachineID
                    LEFT JOIN reject_category rc ON r.CategoryID = rc.CategoryID
                    LEFT JOIN reject_reason rr ON r.ReasonID = rr.ReasonID
                    WHERE 1=1";

            $params = [];

            if (!empty($filterOrder)) { $sql .= " AND r.OrderID = ?"; $params[] = $filterOrder; }
            if (!empty($filterArticle)) { $sql .= " AND r.ArticleID = ?"; $params[] = $filterArticle; }
            if (!empty($filterOperator)) { $sql .= " AND r.OperatorID = ?"; $params[] = $filterOperator; }
            if (!empty($filterCategory)) { $sql .= " AND r.CategoryID = ?"; $params[] = $filterCategory; }
            if (!empty($filterReason)) { $sql .= " AND r.ReasonID = ?"; $params[] = $filterReason; }
            if (!empty($startDate)) { $sql .= " AND r.RejectDate >= ?"; $params[] = $startDate . " 00:00:00"; }
            if (!empty($endDate)) { $sql .= " AND r.RejectDate <= ?"; $params[] = $endDate . " 23:59:59"; }

            $sql .= " ORDER BY r.RejectDate DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getRecentRejects(int $machineId, int $limit = 3): array
    {
        try {
            $sql = "SELECT
                        r.*,
                        rr.ReasonName,
                        rc.CategoryName
                    FROM $this->tableName r
                    LEFT JOIN reject_reason rr ON r.ReasonID = rr.ReasonID
                    LEFT JOIN reject_category rc ON r.CategoryID = rc.CategoryID
                    WHERE r.MachineID = ?
                    ORDER BY r.RejectDate DESC
                    LIMIT ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(1, $machineId, PDO::PARAM_INT);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // --- Helpers for Dropdowns ---

    public function getCategories(): array {
        return $this->pdo->query("SELECT * FROM reject_category ORDER BY CategoryName")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReasons(): array {
        return $this->pdo->query("SELECT * FROM reject_reason ORDER BY ReasonName")->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>