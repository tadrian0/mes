<?php
class BatchManager
{
    private $pdo;
    private $tableName = "batch_log";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createBatch(string $batchCode, string $batchType, int $orderId, int $articleId, int $operatorId, int $machineId, float $quantity, ?string $printTime = null, ?string $notes = null): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO $this->tableName (BatchCode, BatchType, ProductionOrderID, ArticleID, OperatorID, MachineID, Quantity, PrintTime, Notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $printTime = empty($printTime) ? date('Y-m-d H:i:s') : $printTime;

            return $stmt->execute([$batchCode, $batchType, $orderId, $articleId, $operatorId, $machineId, $quantity, $printTime, $notes]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateBatch(int $batchId, string $batchCode, string $batchType, int $orderId, int $articleId, int $operatorId, int $machineId, float $quantity, ?string $printTime = null, ?string $notes = null): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE $this->tableName 
                SET BatchCode = ?, BatchType = ?, ProductionOrderID = ?, ArticleID = ?, OperatorID = ?, MachineID = ?, Quantity = ?, PrintTime = ?, Notes = ?
                WHERE BatchID = ?
            ");

            $printTime = empty($printTime) ? date('Y-m-d H:i:s') : $printTime;

            return $stmt->execute([$batchCode, $batchType, $orderId, $articleId, $operatorId, $machineId, $quantity, $printTime, $notes, $batchId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteBatch(int $batchId): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM $this->tableName WHERE BatchID = ?");
            return $stmt->execute([$batchId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function listBatches(?int $filterMachine = null, ?int $filterOperator = null, ?string $startDate = null, ?string $endDate = null, ?string $searchBatch = null): array
    {
        try {
            $sql = "SELECT 
                        b.*,
                        u.OperatorUsername,
                        m.Name AS MachineName,
                        a.Name AS ArticleName,
                        a.Description AS ArticleDesc
                    FROM $this->tableName b
                    LEFT JOIN user u ON b.OperatorID = u.OperatorID
                    LEFT JOIN machine m ON b.MachineID = m.MachineID
                    LEFT JOIN article a ON b.ArticleID = a.ArticleID
                    WHERE 1=1";

            $params = [];

            if (!empty($filterMachine)) {
                $sql .= " AND b.MachineID = ?";
                $params[] = $filterMachine;
            }
            if (!empty($filterOperator)) {
                $sql .= " AND b.OperatorID = ?";
                $params[] = $filterOperator;
            }
            if (!empty($startDate)) {
                $sql .= " AND b.PrintTime >= ?";
                $params[] = $startDate . " 00:00:00";
            }
            if (!empty($endDate)) {
                $sql .= " AND b.PrintTime <= ?";
                $params[] = $endDate . " 23:59:59";
            }
            if (!empty($searchBatch)) {
                $sql .= " AND b.BatchCode LIKE ?";
                $params[] = "%$searchBatch%";
            }

            $sql .= " ORDER BY b.PrintTime DESC";

            if (empty($filterMachine) && empty($filterOperator) && empty($startDate) && empty($endDate) && empty($searchBatch)) {
                $sql .= " LIMIT 1000";
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>