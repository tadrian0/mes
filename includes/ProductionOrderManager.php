<?php
class ProductionOrderManager
{
    private $pdo;
    private $tableName = "production_order";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    private function calculateEndDate($recipeId, $quantity, $startDate) {
        if (!$recipeId || !$quantity || !$startDate) return null;

        $stmt = $this->pdo->prepare("SELECT EstimatedTime FROM production_recipes WHERE RecipeID = ?");
        $stmt->execute([$recipeId]);
        $cycleTime = $stmt->fetchColumn(); 

        if ($cycleTime) {
            $totalSeconds = $cycleTime * $quantity;
            $start = new DateTime($startDate);
            $start->modify("+{$totalSeconds} seconds");
            return $start->format('Y-m-d H:i:s');
        }
        return null;
    }

    public function createOrder(int $articleId, ?int $recipeId, float $quantity, string $startDate, ?string $endDate = null, string $status = 'Planned'): bool
    {
        try {
            if (empty($endDate) && $recipeId) {
                $endDate = $this->calculateEndDate($recipeId, $quantity, $startDate);
            }

            $stmt = $this->pdo->prepare("
                INSERT INTO {$this->tableName} 
                (ArticleID, RecipeID, TargetQuantity, PlannedStartDate, PlannedEndDate, Status, IsDeleted, CreatedAt)
                VALUES (?, ?, ?, ?, ?, ?, 0, NOW())
            ");
            
            $endDate = empty($endDate) ? null : $endDate;
            
            return $stmt->execute([$articleId, $recipeId, $quantity, $startDate, $endDate, $status]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateOrder(int $orderId, int $articleId, ?int $recipeId, float $quantity, string $startDate, ?string $endDate, string $status): bool
    {
        try {
            if (empty($endDate) && $recipeId) {
                $endDate = $this->calculateEndDate($recipeId, $quantity, $startDate);
            } else {
                $endDate = empty($endDate) ? null : $endDate;
            }
            
            $stmt = $this->pdo->prepare("
                UPDATE {$this->tableName} 
                SET ArticleID = ?, RecipeID = ?, TargetQuantity = ?, PlannedStartDate = ?, PlannedEndDate = ?, Status = ?
                WHERE OrderID = ?
            ");
            
            return $stmt->execute([$articleId, $recipeId, $quantity, $startDate, $endDate, $status, $orderId]);
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

    public function listOrders(bool $showDeleted = false, ?string $search = null, ?int $filterArticle = null, ?string $filterStatus = null, ?string $startDate = null, ?string $endDate = null): array
    {
        try {
            $sql = "SELECT 
                        po.*, 
                        a.Name as ArticleName,
                        u.OperatorUsername as DeletedByUser,
                        pr.Version as RecipeVersion,
                        m.Name as TargetMachine
                    FROM {$this->tableName} po
                    LEFT JOIN article a ON po.ArticleID = a.ArticleID
                    LEFT JOIN user u ON po.DeletedBy = u.OperatorID
                    LEFT JOIN production_recipes pr ON po.RecipeID = pr.RecipeID
                    LEFT JOIN machine m ON pr.MachineID = m.MachineID
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

    public function getActiveOrderForMachine(int $machineId): ?array
    {
        $sql = "SELECT 
                    po.*,
                    a.Name as ArticleName,
                    a.Description as ArticleDesc,
                    pr.EstimatedTime as CycleTime
                FROM {$this->tableName} po
                LEFT JOIN article a ON po.ArticleID = a.ArticleID
                LEFT JOIN production_recipes pr ON po.RecipeID = pr.RecipeID
                WHERE po.Status = 'Active' 
                AND pr.MachineID = ?
                LIMIT 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$machineId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getPlannedOrders(int $machineId): array
    {
        $sql = "SELECT 
                    po.*,
                    a.Name as ArticleName,
                    pr.Version,
                    pr.EstimatedTime
                FROM {$this->tableName} po
                LEFT JOIN article a ON po.ArticleID = a.ArticleID
                LEFT JOIN production_recipes pr ON po.RecipeID = pr.RecipeID
                WHERE po.Status = 'Planned' 
                AND po.IsDeleted = 0
                AND pr.MachineID = ?
                ORDER BY po.PlannedStartDate ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$machineId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function startOrder(int $orderId): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE {$this->tableName} 
                SET Status = 'Active', 
                    ActualStartDate = NOW()
                WHERE OrderID = ?
            ");
            return $stmt->execute([$orderId]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>