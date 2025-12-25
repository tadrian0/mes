<?php
class RawMaterialManager
{
    private $pdo;
    private $tableName = "raw_material_log";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createLog(int $productionOrderId, int $operatorId, string $batchCode, ?int $articleId = null, ?int $machineId = null, float $quantity = 1.0, ?string $scanTime = null, ?string $notes = null): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO $this->tableName (ProductionOrderID, OperatorID, BatchCode, ArticleID, MachineID, Quantity, ScanTime, Notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            // Default to NOW() if scanTime is empty
            $scanTime = empty($scanTime) ? date('Y-m-d H:i:s') : $scanTime;

            return $stmt->execute([$productionOrderId, $operatorId, $batchCode, $articleId, $machineId, $quantity, $scanTime, $notes]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateLog(int $logId, int $productionOrderId, int $operatorId, string $batchCode, ?int $articleId = null, ?int $machineId = null, float $quantity = 1.0, ?string $scanTime = null, ?string $notes = null): bool
    {
        try {
            $sql = "UPDATE $this->tableName 
                    SET ProductionOrderID = ?, OperatorID = ?, BatchCode = ?, ArticleID = ?, MachineID = ?, Quantity = ?, ScanTime = ?, Notes = ?
                    WHERE LogID = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $scanTime = empty($scanTime) ? date('Y-m-d H:i:s') : $scanTime;
            
            return $stmt->execute([$productionOrderId, $operatorId, $batchCode, $articleId, $machineId, $quantity, $scanTime, $notes, $logId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteLog(int $logId): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM $this->tableName WHERE LogID = ?");
            return $stmt->execute([$logId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function listLogs(?int $filterOrder = null, ?string $filterBatch = null, ?string $startDate = null, ?string $endDate = null): array
    {
        try {
            $sql = "SELECT 
                        l.*,
                        u.OperatorUsername,
                        a.Name AS ArticleName,
                        m.Name AS MachineName,
                        po.OrderID AS OrderRef
                    FROM $this->tableName l
                    LEFT JOIN user u ON l.OperatorID = u.OperatorID
                    LEFT JOIN article a ON l.ArticleID = a.ArticleID
                    LEFT JOIN machine m ON l.MachineID = m.MachineID
                    LEFT JOIN production_order po ON l.ProductionOrderID = po.OrderID
                    WHERE 1=1";

            $params = [];

            if (!empty($filterOrder)) {
                $sql .= " AND l.ProductionOrderID = ?";
                $params[] = $filterOrder;
            }
            if (!empty($filterBatch)) {
                $sql .= " AND l.BatchCode LIKE ?";
                $params[] = "%$filterBatch%";
            }
            if (!empty($startDate)) {
                $sql .= " AND l.ScanTime >= ?";
                $params[] = $startDate . " 00:00:00";
            }
            if (!empty($endDate)) {
                $sql .= " AND l.ScanTime <= ?";
                $params[] = $endDate . " 23:59:59";
            }

            $sql .= " ORDER BY l.ScanTime DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>