<?php
class SectionManager {
    private $pdo;
    private $tableName = "section";

    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function create($name, $plantId, $desc, $floorArea, $capacity): bool {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO $this->tableName (Name, PlantID, Description, FloorAreaSqM, MaxCapacity) VALUES (?,?,?,?,?)");
            return $stmt->execute([$name, $plantId, $desc, $floorArea, $capacity]);
        } catch (PDOException $e) { return false; }
    }

    public function update($id, $name, $plantId, $desc, $floorArea, $capacity): bool {
        try {
            $stmt = $this->pdo->prepare("UPDATE $this->tableName SET Name=?, PlantID=?, Description=?, FloorAreaSqM=?, MaxCapacity=? WHERE SectionID=?");
            return $stmt->execute([$name, $plantId, $desc, $floorArea, $capacity, $id]);
        } catch (PDOException $e) { return false; }
    }

    public function delete($id): bool {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM $this->tableName WHERE SectionID=?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) { return false; }
    }

    public function listAll(?int $plantId = null, ?string $search = null): array {
        $sql = "SELECT s.*, p.Name as PlantName, c.Name as CityName 
                FROM $this->tableName s
                LEFT JOIN plant p ON s.PlantID = p.PlantID
                LEFT JOIN city c ON p.CityID = c.CityID
                WHERE 1=1";
        $params = [];
        if ($plantId) { $sql .= " AND s.PlantID = ?"; $params[] = $plantId; }
        if ($search) { $sql .= " AND s.Name LIKE ?"; $params[] = "%$search%"; }
        
        $sql .= " ORDER BY s.Name ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>