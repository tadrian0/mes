<?php
class ProductionOrderManager
{
    private $pdo;
    private $tableName = "production_order";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createOrder(int $articleId, float $quantity, string $startDate, ?string $endDate = null, string $status = 'Planned'): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO {$this->tableName} 
                (ArticleID, TargetQuantity, PlannedStartDate, PlannedEndDate, Status, IsDeleted, CreatedAt)
                VALUES (?, ?, ?, ?, ?, 0, NOW())
            ");
            
            $endDate = empty($endDate) ? null : $endDate;
            
            return $stmt->execute([$articleId, $quantity, $startDate, $endDate, $status]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function updateOrder(int $orderId, int $articleId, float $quantity, string $startDate, ?string $endDate, string $status): bool
    {
        try {
            $endDate = empty($endDate) ? null : $endDate;
            
            $stmt = $this->pdo->prepare("
                UPDATE {$this->tableName} 
                SET ArticleID = ?, TargetQuantity = ?, PlannedStartDate = ?, PlannedEndDate = ?, Status = ?
                WHERE OrderID = ?
            ");
            
            return $stmt->execute([$articleId, $quantity, $startDate, $endDate, $status, $orderId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function softDeleteOrder(int $orderId, int $userId): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE {$this->tableName} 
                SET IsDeleted = 1, DeletedAt = NOW(), DeletedBy = ?
                WHERE OrderID = ?
            ");
            return $stmt->execute([$userId, $orderId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getOrderById(int $orderId): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->tableName} WHERE OrderID = ?");
            $stmt->execute([$orderId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

/**
     * Get the currently running order for a specific machine
     */
    public function getActiveOrderForMachine(int $machineId): ?array
    {
        // We look for an order that is 'Active' AND has an active log on this machine
        // Or simply the order assigned to this machine marked as Active
        $sql = "SELECT 
                    po.*,
                    a.Name as ArticleName,
                    a.Description as ArticleDesc,
                    (SELECT COUNT(*) FROM production_log pl WHERE pl.ProductionOrderID = po.OrderID AND pl.Status = 'Active') as IsRunning
                FROM {$this->tableName} po
                LEFT JOIN article a ON po.ArticleID = a.ArticleID
                WHERE po.MachineID = ? AND po.Status = 'Active'
                LIMIT 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$machineId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get list of planned orders ready to start
     */
    public function getPlannedOrders(int $machineId): array
    {
        // Fetches orders status 'Planned' assigned to this machine OR unassigned (MachineID is NULL)
        $sql = "SELECT 
                    po.*,
                    a.Name as ArticleName
                FROM {$this->tableName} po
                LEFT JOIN article a ON po.ArticleID = a.ArticleID
                WHERE po.Status = 'Planned' 
                AND (po.MachineID = ? OR po.MachineID IS NULL)
                AND po.IsDeleted = 0
                ORDER BY po.PlannedStartDate ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$machineId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Set order to active and assign machine if null
     */
    public function startOrder(int $orderId, int $machineId): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE {$this->tableName} 
                SET Status = 'Active', 
                    ActualStartDate = NOW(),
                    MachineID = ? 
                WHERE OrderID = ?
            ");
            return $stmt->execute([$machineId, $orderId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function listOrders(
        bool $showDeleted = false, 
        ?string $search = null, 
        ?int $filterArticle = null, 
        ?string $filterStatus = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        try {
            $sql = "SELECT 
                        po.*, 
                        a.Name as ArticleName,
                        u.OperatorUsername as DeletedByUser
                    FROM {$this->tableName} po
                    LEFT JOIN article a ON po.ArticleID = a.ArticleID
                    LEFT JOIN user u ON po.DeletedBy = u.OperatorID
                    WHERE po.IsDeleted = ?";

            $params = [$showDeleted ? 1 : 0];

            if (!empty($search)) {
                $sql .= " AND (po.OrderID LIKE ? OR a.Name LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            if (!empty($filterArticle)) {
                $sql .= " AND po.ArticleID = ?";
                $params[] = $filterArticle;
            }
            if (!empty($filterStatus)) {
                $sql .= " AND po.Status = ?";
                $params[] = $filterStatus;
            }
            if (!empty($startDate)) {
                $sql .= " AND po.PlannedStartDate >= ?";
                $params[] = $startDate . " 00:00:00";
            }
            if (!empty($endDate)) {
                $sql .= " AND po.PlannedStartDate <= ?";
                $params[] = $endDate . " 23:59:59";
            }

            $sql .= " ORDER BY po.PlannedStartDate DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>