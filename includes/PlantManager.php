<?php
class PlantManager {
    private $pdo;
    private $tableName = "plant";

    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function create($name, $desc, $cityId, $address, $email, $phone, $manager, $status = 'Active'): bool {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO $this->tableName (Name, Description, CityID, Address, ContactEmail, ContactPhone, ManagerName, Status) VALUES (?,?,?,?,?,?,?,?)");
            return $stmt->execute([$name, $desc, $cityId, $address, $email, $phone, $manager, $status]);
        } catch (PDOException $e) { return false; }
    }

    public function update($id, $name, $desc, $cityId, $address, $email, $phone, $manager, $status): bool {
        try {
            $stmt = $this->pdo->prepare("UPDATE $this->tableName SET Name=?, Description=?, CityID=?, Address=?, ContactEmail=?, ContactPhone=?, ManagerName=?, Status=? WHERE PlantID=?");
            return $stmt->execute([$name, $desc, $cityId, $address, $email, $phone, $manager, $status, $id]);
        } catch (PDOException $e) { return false; }
    }

    public function delete($id): bool {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM $this->tableName WHERE PlantID=?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) { return false; }
    }

    public function listAll(?int $cityId = null, ?string $search = null): array {
        $sql = "SELECT p.*, c.Name as CityName, co.Name as CountryName, co.ISOCode 
                FROM $this->tableName p
                LEFT JOIN city c ON p.CityID = c.CityID
                LEFT JOIN country co ON c.CountryID = co.CountryID
                WHERE 1=1";
        $params = [];
        if ($cityId) { $sql .= " AND p.CityID = ?"; $params[] = $cityId; }
        if ($search) { $sql .= " AND p.Name LIKE ?"; $params[] = "%$search%"; }
        
        $sql .= " ORDER BY p.Name ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>