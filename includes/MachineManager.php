<?php
class MachineManager
{
    private $pdo;
    private $tableName = "machine";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createMachine(string $name, string $status = 'Active', float $capacity = 0.0, ?string $lastMaintenanceDate = null, string $location = '', string $model = ''): bool
    {
        if (empty($name) || empty($location) || empty($model) || $capacity <= 0) {
            return false;
        }
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO $tableName (Name, Status, Capacity, LastMaintenanceDate, Location, Model)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            return $stmt->execute([$name, $status, $capacity, $lastMaintenanceDate, $location, $model]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getMachineById(int $machineId): ?array
    {
        try {
            $stmt = $this->pdo->prepare('
                SELECT MachineID, Name, Status, Capacity, LastMaintenanceDate, Location, Model, CreatedAt, UpdatedAt
                FROM $tableName
                WHERE MachineID = ?
            ');
            $stmt->execute([$machineId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function updateMachine(int $machineId, ?string $name = null, ?string $status = null, ?float $capacity = null, ?string $lastMaintenanceDate = null, ?string $location = null, ?string $model = null): bool
    {
        $updates = [];
        $params = [];
        if ($name !== null && $name !== '') {
            $updates[] = 'Name = ?';
            $params[] = $name;
        }
        if ($status !== null && $status !== '') {
            $updates[] = 'Status = ?';
            $params[] = $status;
        }
        if ($capacity !== null && $capacity > 0) {
            $updates[] = 'Capacity = ?';
            $params[] = $capacity;
        }
        if ($lastMaintenanceDate !== null) {
            $updates[] = 'LastMaintenanceDate = ?';
            $params[] = $lastMaintenanceDate;
        }
        if ($location !== null && $location !== '') {
            $updates[] = 'Location = ?';
            $params[] = $location;
        }
        if ($model !== null && $model !== '') {
            $updates[] = 'Model = ?';
            $params[] = $model;
        }
        if (empty($updates)) {
            return false;
        }
        $params[] = $machineId;
        try {
            $stmt = $this->pdo->prepare('
                UPDATE $tableName
                SET ' . implode(', ', $updates) . '
                WHERE MachineID = ?
            ');
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteMachine(int $machineId): bool
    {
        try {
            $stmt = $this->pdo->prepare('DELETE FROM $tableName WHERE MachineID = ?');
            return $stmt->execute([$machineId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function listMachines(): array
    {
        try {
            $stmt = $this->pdo->query('
                SELECT MachineID, Name, Status, Capacity, LastMaintenanceDate, Location, Model, CreatedAt, UpdatedAt
                FROM $tableName
                ORDER BY Name ASC
            ');
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>