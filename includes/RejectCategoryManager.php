<?php
class RejectCategoryManager
{
    private $pdo;
    private $tableName = "reject_category";
    private $reasonTable = "reject_reason";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(string $name, ?int $plantId, ?int $sectionId): bool
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO $this->tableName (CategoryName, PlantID, SectionID) VALUES (?, ?, ?)");
            return $stmt->execute([$name, $plantId ?: null, $sectionId ?: null]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update(int $id, string $name, ?int $plantId, ?int $sectionId): bool
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE $this->tableName SET CategoryName=?, PlantID=?, SectionID=? WHERE CategoryID=?");
            return $stmt->execute([$name, $plantId ?: null, $sectionId ?: null, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM $this->tableName WHERE CategoryID=?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function listCategories(?int $filterPlant = null, ?int $filterSection = null): array
    {
        try {
            $sql = "SELECT 
                        rc.*, 
                        p.Name AS PlantName, 
                        s.Name AS SectionName,
                        c.Name AS CityName,
                        co.Name AS CountryName,
                        COUNT(rr.ReasonID) as ReasonCount
                    FROM $this->tableName rc
                    LEFT JOIN plant p ON rc.PlantID = p.PlantID
                    LEFT JOIN section s ON rc.SectionID = s.SectionID
                    LEFT JOIN city c ON p.CityID = c.CityID
                    LEFT JOIN country co ON c.CountryID = co.CountryID
                    LEFT JOIN reject_reason rr ON rc.CategoryID = rr.CategoryID
                    WHERE 1=1";

            $params = [];
            if ($filterPlant) {
                $sql .= " AND (rc.PlantID = ? OR rc.PlantID IS NULL)"; 
                $params[] = $filterPlant;
            }
            if ($filterSection) {
                $sql .= " AND (rc.SectionID = ? OR rc.SectionID IS NULL)";
                $params[] = $filterSection;
            }

            $sql .= " GROUP BY rc.CategoryID, p.Name, s.Name, c.Name, co.Name";
            $sql .= " ORDER BY rc.CategoryName ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Copies a category and its reasons to multiple target locations.
     * @param int $sourceCategoryId
     * @param array $targets Array of ['plant_id' => int, 'section_id' => int]
     */
    public function replicateCategory(int $sourceCategoryId, array $targets): bool
    {
        try {
            $this->pdo->beginTransaction();

            $stmtCat = $this->pdo->prepare("SELECT CategoryName FROM $this->tableName WHERE CategoryID = ?");
            $stmtCat->execute([$sourceCategoryId]);
            $sourceCatName = $stmtCat->fetchColumn();

            if (!$sourceCatName) return false;

            $stmtReasons = $this->pdo->prepare("SELECT ReasonName FROM $this->reasonTable WHERE CategoryID = ?");
            $stmtReasons->execute([$sourceCategoryId]);
            $reasons = $stmtReasons->fetchAll(PDO::FETCH_COLUMN);

            $insertCat = $this->pdo->prepare("INSERT INTO $this->tableName (CategoryName, PlantID, SectionID) VALUES (?, ?, ?)");
            $insertReason = $this->pdo->prepare("INSERT INTO $this->reasonTable (ReasonName, CategoryID) VALUES (?, ?)");

            foreach ($targets as $target) {
                $insertCat->execute([$sourceCatName, $target['plant_id'], $target['section_id']]);
                $newCatId = $this->pdo->lastInsertId();

                if (!empty($reasons)) {
                    $reasonValues = [];
                    $reasonParams = [];
                    foreach ($reasons as $reasonName) {
                        $reasonValues[] = "(?, ?)";
                        $reasonParams[] = $reasonName;
                        $reasonParams[] = $newCatId;
                    }
                    $sql = "INSERT INTO $this->reasonTable (ReasonName, CategoryID) VALUES " . implode(", ", $reasonValues);
                    $stmtBatch = $this->pdo->prepare($sql);
                    $stmtBatch->execute($reasonParams);
                }
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