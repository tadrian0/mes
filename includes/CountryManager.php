<?php
class CountryManager {
    private $pdo;
    private $tableName = "country";

    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function create(string $name, string $isoCode): bool {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO $this->tableName (Name, ISOCode) VALUES (?, ?)");
            return $stmt->execute([$name, strtoupper($isoCode)]);
        } catch (PDOException $e) { return false; }
    }

    public function update(int $id, string $name, string $isoCode): bool {
        try {
            $stmt = $this->pdo->prepare("UPDATE $this->tableName SET Name = ?, ISOCode = ? WHERE CountryID = ?");
            return $stmt->execute([$name, strtoupper($isoCode), $id]);
        } catch (PDOException $e) { return false; }
    }

    public function delete(int $id): bool {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM $this->tableName WHERE CountryID = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) { return false; }
    }

    public function listAll(?string $search = null): array {
        $sql = "SELECT * FROM $this->tableName WHERE 1=1";
        $params = [];
        if ($search) {
            $sql .= " AND (Name LIKE ? OR ISOCode LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        $sql .= " ORDER BY Name ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>