<?php
class MachineManager
{
    private $pdo;
    private $tableName = "machine";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createMachine(string $name, string $status = 'Active', float $capacity = 0.0, ?string $lastMaintenanceDate = null, string $location = '', string $model = '', ?int $plantId = null, ?int $sectionId = null): bool
    {
        if (empty($name) || empty($location) || empty($model) || $capacity <= 0) {
            return false;
        }
        
        $lastMaintenanceDate = empty($lastMaintenanceDate) ? null : $lastMaintenanceDate;

        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO $this->tableName (Name, Status, Capacity, LastMaintenanceDate, Location, Model, PlantID, SectionID)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            return $stmt->execute([$name, $status, $capacity, $lastMaintenanceDate, $location, $model, $plantId, $sectionId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getMachineById(int $machineId): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM $this->tableName WHERE MachineID = ?");
            $stmt->execute([$machineId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function updateMachine(int $machineId, ?string $name, ?string $status, ?float $capacity, ?string $lastMaintenanceDate, ?string $location, ?string $model, ?int $plantId, ?int $sectionId): bool
    {
        $updates = [];
        $params = [];

        if ($name !== null) { $updates[] = 'Name = ?'; $params[] = $name; }
        if ($status !== null) { $updates[] = 'Status = ?'; $params[] = $status; }
        if ($capacity !== null) { $updates[] = 'Capacity = ?'; $params[] = $capacity; }
        if ($lastMaintenanceDate !== null) { 
            $updates[] = 'LastMaintenanceDate = ?'; 
            $params[] = empty($lastMaintenanceDate) ? null : $lastMaintenanceDate; 
        }
        if ($location !== null) { $updates[] = 'Location = ?'; $params[] = $location; }
        if ($model !== null) { $updates[] = 'Model = ?'; $params[] = $model; }
        if ($plantId !== null) { $updates[] = 'PlantID = ?'; $params[] = $plantId ?: null; }
        if ($sectionId !== null) { $updates[] = 'SectionID = ?'; $params[] = $sectionId ?: null; }

        if (empty($updates)) { return false; }

        $params[] = $machineId;

        try {
            $sql = "UPDATE $this->tableName SET " . implode(', ', $updates) . " WHERE MachineID = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteMachine(int $machineId): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM $this->tableName WHERE MachineID = ?");
            return $stmt->execute([$machineId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function listMachines(): array
    {
        try {
            $sql = "SELECT 
                        m.*, 
                        p.Name AS PlantName, 
                        s.Name AS SectionName,
                        ci.Name AS CityName,
                        co.Name AS CountryName,
                        co.ISOCode
                    FROM $this->tableName m
                    LEFT JOIN plant p ON m.PlantID = p.PlantID
                    LEFT JOIN section s ON m.SectionID = s.SectionID
                    LEFT JOIN city ci ON p.CityID = ci.CityID
                    LEFT JOIN country co ON ci.CountryID = co.CountryID
                    ORDER BY m.Name ASC";
            
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>