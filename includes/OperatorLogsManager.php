<?php
class OperatorLogsManager
{
    private $pdo;
    private $tableName = "operator_log";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function loginOperator(int $operatorId, int $machineId): bool
    {
        try {
            $this->pdo->beginTransaction();

            $closeStmt = $this->pdo->prepare("
                UPDATE $this->tableName 
                SET LogoutTime = NOW() 
                WHERE OperatorID = ? AND LogoutTime IS NULL
            ");
            $closeStmt->execute([$operatorId]);

            $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM $this->tableName WHERE MachineID = ? AND LogoutTime IS NULL");
            $countStmt->execute([$machineId]);
            if ($countStmt->fetchColumn() >= 6) {
                $this->pdo->rollBack();
                return false;
            }

            $stmt = $this->pdo->prepare("
                INSERT INTO $this->tableName (OperatorID, MachineID, LoginTime)
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$operatorId, $machineId]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function logoutOperator(int $operatorId): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE $this->tableName 
                SET LogoutTime = NOW() 
                WHERE OperatorID = ? AND LogoutTime IS NULL
            ");
            return $stmt->execute([$operatorId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getActiveOperators(int $machineId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    l.LogID,
                    l.OperatorID,
                    l.LoginTime,
                    u.OperatorUsername,
                    u.OperatorRoles
                FROM $this->tableName l
                JOIN user u ON l.OperatorID = u.OperatorID
                WHERE l.MachineID = ? AND l.LogoutTime IS NULL
                ORDER BY l.LoginTime ASC
            ");
            $stmt->execute([$machineId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function createLogManual(int $operatorId, int $machineId, string $loginTime, ?string $logoutTime = null, ?string $notes = null): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO $this->tableName (OperatorID, MachineID, LoginTime, LogoutTime, Notes)
                VALUES (?, ?, ?, ?, ?)
            ");
            $logoutTime = empty($logoutTime) ? null : $logoutTime;
            return $stmt->execute([$operatorId, $machineId, $loginTime, $logoutTime, $notes]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateLog(int $logId, int $operatorId, int $machineId, string $loginTime, ?string $logoutTime = null, ?string $notes = null): bool
    {
        try {
            $logoutTime = empty($logoutTime) ? null : $logoutTime;
            
            $stmt = $this->pdo->prepare("
                UPDATE $this->tableName 
                SET OperatorID = ?, MachineID = ?, LoginTime = ?, LogoutTime = ?, Notes = ?
                WHERE LogID = ?
            ");
            return $stmt->execute([$operatorId, $machineId, $loginTime, $logoutTime, $notes, $logId]);
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

    public function listLogs(?int $filterMachine = null, ?int $filterOperator = null, ?string $startDate = null, ?string $endDate = null): array
    {
        try {
            $sql = "SELECT 
                        l.LogID, 
                        l.OperatorID, 
                        l.MachineID, 
                        l.LoginTime, 
                        l.LogoutTime, 
                        l.DurationMinutes, 
                        l.Notes,
                        u.OperatorUsername AS OperatorName, 
                        m.Name AS MachineName
                    FROM $this->tableName l
                    LEFT JOIN user u ON l.OperatorID = u.OperatorID
                    LEFT JOIN machine m ON l.MachineID = m.MachineID
                    WHERE 1=1";

            $params = [];

            if (!empty($filterMachine)) {
                $sql .= " AND l.MachineID = ?";
                $params[] = $filterMachine;
            }
            if (!empty($filterOperator)) {
                $sql .= " AND l.OperatorID = ?";
                $params[] = $filterOperator;
            }
            if (!empty($startDate)) {
                $sql .= " AND l.LoginTime >= ?";
                $params[] = $startDate . " 00:00:00";
            }
            if (!empty($endDate)) {
                $sql .= " AND l.LoginTime <= ?";
                $params[] = $endDate . " 23:59:59";
            }

            $sql .= " ORDER BY l.LoginTime DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>