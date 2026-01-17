<?php
class ProductionOrderManager
{
    private $pdo;
    private $tableName = "production_order";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Create a new Production Order
     */
    public function createOrder(int $articleId, float $quantity, string $startDate, ?string $endDate = null, string $status = 'Planned'): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO $this->tableName (ArticleID, Quantity, StartDate, EndDate, Status, IsDeleted)
                VALUES (?, ?, ?, ?, ?, 0)
            ");

            // Format dates or handle nulls
            $endDate = empty($endDate) ? null : $endDate;

            return $stmt->execute([$articleId, $quantity, $startDate, $endDate, $status]);
        } catch (PDOException $e) {
            // Log error here if needed
            return false;
        }
    }

    /**
     * Update an existing Order
     */
    public function updateOrder(int $orderId, int $articleId, float $quantity, string $startDate, ?string $endDate, string $status): bool
    {
        try {
            $endDate = empty($endDate) ? null : $endDate;

            $stmt = $this->pdo->prepare("
                UPDATE $this->tableName 
                SET ArticleID = ?, Quantity = ?, StartDate = ?, EndDate = ?, Status = ?
                WHERE OrderID = ?
            ");
            return $stmt->execute([$articleId, $quantity, $startDate, $endDate, $status, $orderId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Soft Delete: Marks as deleted but keeps data
     * @param int $orderId The order to delete
     * @param int $userId The ID of the admin/user performing the delete
     */
    public function softDeleteOrder(int $orderId, int $userId): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE $this->tableName 
                SET IsDeleted = 1, 
                    DeletedAt = NOW(), 
                    DeletedBy = ?
                WHERE OrderID = ?
            ");
            return $stmt->execute([$userId, $orderId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Restore a soft-deleted order (Optional utility)
     */
    public function restoreOrder(int $orderId): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE $this->tableName 
                SET IsDeleted = 0, DeletedAt = NULL, DeletedBy = NULL
                WHERE OrderID = ?
            ");
            return $stmt->execute([$orderId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * List orders with filtering
     * @param bool $showDeleted If true, shows ONLY deleted items. If false (default), shows only active.
     */
    public function listOrders(bool $showDeleted = false, ?string $search = null): array
    {
        try {
            $sql = "SELECT 
                        po.*, 
                        a.Name as ArticleName,
                        u.OperatorUsername as DeletedByUser
                    FROM $this->tableName po
                    LEFT JOIN article a ON po.ArticleID = a.ArticleID
                    LEFT JOIN user u ON po.DeletedBy = u.OperatorID
                    WHERE po.IsDeleted = ?";

            $params = [$showDeleted ? 1 : 0];

            if ($search) {
                $sql .= " AND (po.OrderID LIKE ? OR a.Name LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            $sql .= " ORDER BY po.StartDate DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getOrderById(int $orderId): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM $this->tableName WHERE OrderID = ?");
            $stmt->execute([$orderId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }
}
?>