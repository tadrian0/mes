<?php
class WagoManager
{
    private $pdo;
    private $tableName = "wago";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function logSignal(int $machineId, int $count): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO $this->tableName (MachineID, ProductionCount, Timestamp, Processed)
                VALUES (?, ?, NOW(), 0)
            ");
            return $stmt->execute([$machineId, $count]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function listLogs(?int $filterMachine = null, ?string $startDate = null, ?string $endDate = null): array
    {
        try {
            $sql = "SELECT 
                        w.*, 
                        m.Name AS MachineName,
                        m.Location
                    FROM $this->tableName w
                    LEFT JOIN machine m ON w.MachineID = m.MachineID
                    WHERE 1=1";

            $params = [];

            if (!empty($filterMachine)) {
                $sql .= " AND w.MachineID = ?";
                $params[] = $filterMachine;
            }
            if (!empty($startDate)) {
                $sql .= " AND w.Timestamp >= ?";
                $params[] = $startDate . " 00:00:00";
            }
            if (!empty($endDate)) {
                $sql .= " AND w.Timestamp <= ?";
                $params[] = $endDate . " 23:59:59";
            }

            $sql .= " ORDER BY w.Timestamp DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>