<?php
class CityManager {
    private $pdo;
    private $tableName = "city";

    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function create(string $name, int $countryId, string $postalCode): bool {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO $this->tableName (Name, CountryID, PostalCode) VALUES (?, ?, ?)");
            return $stmt->execute([$name, $countryId, $postalCode]);
        } catch (PDOException $e) { return false; }
    }

    public function update(int $id, string $name, int $countryId, string $postalCode): bool {
        try {
            $stmt = $this->pdo->prepare("UPDATE $this->tableName SET Name = ?, CountryID = ?, PostalCode = ? WHERE CityID = ?");
            return $stmt->execute([$name, $countryId, $postalCode, $id]);
        } catch (PDOException $e) { return false; }
    }

    public function delete(int $id): bool {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM $this->tableName WHERE CityID = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) { return false; }
    }

    public function listAll(?int $countryId = null, ?string $search = null): array {
        $sql = "SELECT c.*, co.Name as CountryName, co.ISOCode 
                FROM $this->tableName c 
                LEFT JOIN country co ON c.CountryID = co.CountryID 
                WHERE 1=1";
        $params = [];
        if ($countryId) { $sql .= " AND c.CountryID = ?"; $params[] = $countryId; }
        if ($search) { $sql .= " AND c.Name LIKE ?"; $params[] = "%$search%"; }
        
        $sql .= " ORDER BY c.Name ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>