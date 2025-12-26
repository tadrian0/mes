<?php
class MachineStopManager
{
    private $pdo;
    private $tableName = "machine_stop_log";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Start a downtime event
     */
    public function startStop(int $machineId, int $operatorId, ?int $orderId = null, ?string $startTime = null, ?string $notes = null): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO $this->tableName (MachineID, OperatorID, ProductionOrderID, StartTime, Notes)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $startTime = empty($startTime) ? date('Y-m-d H:i:s') : $startTime;

            return $stmt->execute([$machineId, $operatorId, $orderId, $startTime, $notes]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * End a downtime event and assign reason
     */
    public function endStop(int $stopId, int $categoryId, int $reasonId, ?string $endTime = null, ?string $notes = null): bool
    {
        try {
            $endTime = empty($endTime) ? date('Y-m-d H:i:s') : $endTime;
            
            $stmt = $this->pdo->prepare("
                UPDATE $this->tableName 
                SET EndTime = ?, CategoryID = ?, ReasonID = ?, Notes = ?
                WHERE StopID = ?
            ");
            
            return $stmt->execute([$endTime, $categoryId, $reasonId, $notes, $stopId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateStop(int $stopId, int $machineId, int $operatorId, ?int $orderId, ?int $catId, ?int $reasonId, string $start, ?string $end, string $notes): bool
    {
        try {
            $end = empty($end) ? null : $end;
            $orderId = empty($orderId) ? null : $orderId;
            $catId = empty($catId) ? null : $catId;
            $reasonId = empty($reasonId) ? null : $reasonId;

            $stmt = $this->pdo->prepare("
                UPDATE $this->tableName
                SET MachineID = ?, OperatorID = ?, ProductionOrderID = ?, CategoryID = ?, ReasonID = ?, StartTime = ?, EndTime = ?, Notes = ?
                WHERE StopID = ?
            ");
            return $stmt->execute([$machineId, $operatorId, $orderId, $catId, $reasonId, $start, $end, $notes, $stopId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteStop(int $stopId): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM $this->tableName WHERE StopID = ?");
            return $stmt->execute([$stopId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function listStops(?int $filterMachine = null, ?int $filterOperator = null, ?int $filterCategory = null, ?int $filterReason = null, ?string $startDate = null, ?string $endDate = null): array
    {
        try {
            $sql = "SELECT 
                        sl.*,
                        m.Name AS MachineName,
                        u.OperatorUsername,
                        po.OrderID AS OrderRef,
                        c.CategoryName,
                        r.ReasonName
                    FROM $this->tableName sl
                    LEFT JOIN machine m ON sl.MachineID = m.MachineID
                    LEFT JOIN user u ON sl.OperatorID = u.OperatorID
                    LEFT JOIN production_order po ON sl.ProductionOrderID = po.OrderID
                    LEFT JOIN machine_stop_category c ON sl.CategoryID = c.CategoryID
                    LEFT JOIN machine_stop_reason r ON sl.ReasonID = r.ReasonID
                    WHERE 1=1";

            $params = [];

            if (!empty($filterMachine)) { $sql .= " AND sl.MachineID = ?"; $params[] = $filterMachine; }
            if (!empty($filterOperator)) { $sql .= " AND sl.OperatorID = ?"; $params[] = $filterOperator; }
            if (!empty($filterCategory)) { $sql .= " AND sl.CategoryID = ?"; $params[] = $filterCategory; }
            if (!empty($filterReason)) { $sql .= " AND sl.ReasonID = ?"; $params[] = $filterReason; }
            if (!empty($startDate)) { $sql .= " AND sl.StartTime >= ?"; $params[] = $startDate . " 00:00:00"; }
            if (!empty($endDate)) { $sql .= " AND sl.StartTime <= ?"; $params[] = $endDate . " 23:59:59"; }

            $sql .= " ORDER BY sl.StartTime DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getCategories(): array {
        return $this->pdo->query("SELECT * FROM machine_stop_category ORDER BY CategoryName")->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getReasons(): array {
        return $this->pdo->query("SELECT * FROM machine_stop_reason ORDER BY ReasonName")->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>