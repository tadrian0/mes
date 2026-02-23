<?php
class RejectReasonManager
{
    private $pdo;
    private $tableName = "reject_reason";
    private $categoryTable = "reject_category";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(string $name, int $categoryId, ?int $plantId, ?int $sectionId): bool
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO $this->tableName (ReasonName, CategoryID, PlantID, SectionID) VALUES (?, ?, ?, ?)");
            return $stmt->execute([$name, $categoryId, $plantId ?: null, $sectionId ?: null]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update(int $id, string $name, int $categoryId, ?int $plantId, ?int $sectionId): bool
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE $this->tableName SET ReasonName=?, CategoryID=?, PlantID=?, SectionID=? WHERE ReasonID=?");
            return $stmt->execute([$name, $categoryId, $plantId ?: null, $sectionId ?: null, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM $this->tableName WHERE ReasonID=?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function listReasons(?int $filterPlant = null, ?int $filterSection = null, ?int $filterCategory = null): array
    {
        try {
            $sql = "SELECT 
                        rr.*, 
                        rc.CategoryName,
                        p.Name AS PlantName, 
                        s.Name AS SectionName,
                        c.Name AS CityName,
                        co.Name AS CountryName
                    FROM $this->tableName rr
                    LEFT JOIN $this->categoryTable rc ON rr.CategoryID = rc.CategoryID
                    LEFT JOIN plant p ON rr.PlantID = p.PlantID
                    LEFT JOIN section s ON rr.SectionID = s.SectionID
                    LEFT JOIN city c ON p.CityID = c.CityID
                    LEFT JOIN country co ON c.CountryID = co.CountryID
                    WHERE 1=1";

            $params = [];
            if ($filterPlant) {
                $sql .= " AND (rr.PlantID = ? OR rr.PlantID IS NULL)"; 
                $params[] = $filterPlant;
            }
            if ($filterSection) {
                $sql .= " AND (rr.SectionID = ? OR rr.SectionID IS NULL)";
                $params[] = $filterSection;
            }
            if ($filterCategory) {
                $sql .= " AND rr.CategoryID = ?";
                $params[] = $filterCategory;
            }

            $sql .= " ORDER BY rc.CategoryName ASC, rr.ReasonName ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getCategories(): array {
        return $this->pdo->query("SELECT * FROM $this->categoryTable ORDER BY CategoryName")->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Copies a reason to multiple target locations.
     * @param int $sourceReasonId
     * @param array $targets Array of ['plant_id' => int|null, 'section_id' => int|null]
     */
    public function replicateReason(int $sourceReasonId, array $targets): bool
    {
        try {
            $this->pdo->beginTransaction();

            $stmtSrc = $this->pdo->prepare("SELECT ReasonName, CategoryID FROM $this->tableName WHERE ReasonID = ?");
            $stmtSrc->execute([$sourceReasonId]);
            $sourceData = $stmtSrc->fetch(PDO::FETCH_ASSOC);

            if (!$sourceData) return false;

            $values = [];
            $params = [];
            foreach ($targets as $target) {
                $values[] = "(?, ?, ?, ?)";
                $params[] = $sourceData['ReasonName'];
                $params[] = $sourceData['CategoryID'];
                $params[] = $target['plant_id'];
                $params[] = $target['section_id'];
            }

            if (!empty($values)) {
                $sql = "INSERT INTO $this->tableName (ReasonName, CategoryID, PlantID, SectionID) VALUES " . implode(", ", $values);
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
}
?>