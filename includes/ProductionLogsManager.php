<?php
class ProductionLogsManager
{
    private $pdo;
    private $tableName = "production_log";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Start a new production run
     */
    public function startLog(int $orderId, int $machineId, int $startOperatorId, ?string $startTime = null, ?string $notes = null): bool
    {
        try {
            // Optional: Check if there is already an active log for this machine/order and close it?
            // For now, we allow starting new ones freely.

            $stmt = $this->pdo->prepare("
                INSERT INTO $this->tableName (ProductionOrderID, MachineID, StartOperatorID, StartTime, Status, Notes)
                VALUES (?, ?, ?, ?, 'Active', ?)
            ");
            
            $startTime = empty($startTime) ? date('Y-m-d H:i:s') : $startTime;

            return $stmt->execute([$orderId, $machineId, $startOperatorId, $startTime, $notes]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Stop/Close an active run
     */
    public function stopLog(int $logId, int $endOperatorId, ?string $endTime = null, float $shiftCount = 0.0): bool
    {
        try {
            $endTime = empty($endTime) ? date('Y-m-d H:i:s') : $endTime;
            
            $stmt = $this->pdo->prepare("
                UPDATE $this->tableName 
                SET EndOperatorID = ?, EndTime = ?, Status = 'Closed', ShiftCount = ?
                WHERE LogID = ?
            ");
            
            return $stmt->execute([$endOperatorId, $endTime, $shiftCount, $logId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Admin Update
    public function updateLog(int $logId, int $orderId, int $machineId, int $startOpId, ?int $endOpId, string $start, ?string $end, string $status, string $notes): bool
    {
        try {
            $end = empty($end) ? null : $end;
            $endOpId = empty($endOpId) ? null : $endOpId;

            $stmt = $this->pdo->prepare("
                UPDATE $this->tableName
                SET ProductionOrderID = ?, MachineID = ?, StartOperatorID = ?, EndOperatorID = ?, StartTime = ?, EndTime = ?, Status = ?, Notes = ?
                WHERE LogID = ?
            ");
            return $stmt->execute([$orderId, $machineId, $startOpId, $endOpId, $start, $end, $status, $notes, $logId]);
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

    public function listLogs(?int $filterMachine = null, ?int $filterOperator = null, ?int $filterOrder = null, ?string $startDate = null, ?string $endDate = null): array
    {
        try {
            $sql = "SELECT 
                        pl.*,
                        uStart.OperatorUsername AS StartOperatorName,
                        uEnd.OperatorUsername AS EndOperatorName,
                        m.Name AS MachineName,
                        a.Name AS ArticleName,
                        a.ArticleID
                    FROM $this->tableName pl
                    LEFT JOIN user uStart ON pl.StartOperatorID = uStart.OperatorID
                    LEFT JOIN user uEnd ON pl.EndOperatorID = uEnd.OperatorID
                    LEFT JOIN machine m ON pl.MachineID = m.MachineID
                    LEFT JOIN production_order po ON pl.ProductionOrderID = po.OrderID
                    LEFT JOIN article a ON po.ArticleID = a.ArticleID
                    WHERE 1=1";

            $params = [];

            if (!empty($filterMachine)) {
                $sql .= " AND pl.MachineID = ?";
                $params[] = $filterMachine;
            }
            if (!empty($filterOperator)) {
                $sql .= " AND (pl.StartOperatorID = ? OR pl.EndOperatorID = ?)";
                $params[] = $filterOperator;
                $params[] = $filterOperator;
            }
            if (!empty($filterOrder)) {
                $sql .= " AND pl.ProductionOrderID = ?";
                $params[] = $filterOrder;
            }
            if (!empty($startDate)) {
                $sql .= " AND pl.StartTime >= ?";
                $params[] = $startDate . " 00:00:00";
            }
            if (!empty($endDate)) {
                $sql .= " AND pl.StartTime <= ?";
                $params[] = $endDate . " 23:59:59";
            }

            $sql .= " ORDER BY pl.StartTime DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>