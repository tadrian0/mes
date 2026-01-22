<?php
class RecipeManager
{
    private $pdo;
    private $tableName = "production_recipes";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createRecipe(int $articleId, int $machineId, string $version, float $estimatedTime, string $opDesc, int $isActive = 1, ?string $notes = null): bool
    {
        try {
            // Optional: If setting as Active, you might want to set others of this Article to Inactive (logic depends on requirements)
            
            $stmt = $this->pdo->prepare("
                INSERT INTO $this->tableName (ArticleID, MachineID, Version, EstimatedTime, OperationDescription, IsActive, Notes)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            return $stmt->execute([$articleId, $machineId, $version, $estimatedTime, $opDesc, $isActive, $notes]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateRecipe(int $recipeId, int $articleId, int $machineId, string $version, float $estimatedTime, string $opDesc, int $isActive, ?string $notes): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE $this->tableName 
                SET ArticleID=?, MachineID=?, Version=?, EstimatedTime=?, OperationDescription=?, IsActive=?, Notes=?
                WHERE RecipeID=?
            ");
            return $stmt->execute([$articleId, $machineId, $version, $estimatedTime, $opDesc, $isActive, $notes, $recipeId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteRecipe(int $recipeId): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM $this->tableName WHERE RecipeID = ?");
            return $stmt->execute([$recipeId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getRecipeById(int $id): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM $this->tableName WHERE RecipeID = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * List recipes with filters and joins
     */
    public function listRecipes(?int $filterArticle = null, ?int $filterMachine = null, ?string $search = null): array
    {
        try {
            $sql = "SELECT 
                        r.*,
                        a.Name AS ArticleName,
                        a.ArticleID,
                        m.Name AS MachineName,
                        m.Location AS MachineLoc
                    FROM $this->tableName r
                    JOIN article a ON r.ArticleID = a.ArticleID
                    JOIN machine m ON r.MachineID = m.MachineID
                    WHERE 1=1";

            $params = [];

            if (!empty($filterArticle)) {
                $sql .= " AND r.ArticleID = ?";
                $params[] = $filterArticle;
            }
            if (!empty($filterMachine)) {
                $sql .= " AND r.MachineID = ?";
                $params[] = $filterMachine;
            }
            if (!empty($search)) {
                $sql .= " AND (r.Version LIKE ? OR r.OperationDescription LIKE ? OR a.Name LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            // Order by Article Name then Active status (Active first) then Version
            $sql .= " ORDER BY a.Name ASC, r.IsActive DESC, r.Version ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}


class RecipeInputManager
{
    private $pdo;
    private $tableName = "recipe_inputs";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createInput(int $recipeId, int $articleId, float $quantity = 1.0, string $unit = 'unit', string $inputType = 'part'): bool
    {
        if ($recipeId <= 0 || $articleId <= 0 || $quantity <= 0) {
            return false;
        }
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO $this->tableName (RecipeID, ArticleID, Quantity, Unit, InputType)
                VALUES (?, ?, ?, ?, ?)
            ");
            return $stmt->execute([$recipeId, $articleId, $quantity, $unit, $inputType]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getInputById(int $inputId): ?array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT InputID, RecipeID, ArticleID, Quantity, Unit, InputType, CreatedAt, UpdatedAt
                FROM $this->tableName
                WHERE InputID = ?
            ");
            $stmt->execute([$inputId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function updateInput(int $inputId, ?int $recipeId = null, ?int $articleId = null, ?float $quantity = null, ?string $unit = null, ?string $inputType = null): bool
    {
        $updates = [];
        $params = [];
        if ($recipeId !== null && $recipeId > 0) {
            $updates[] = 'RecipeID = ?';
            $params[] = $recipeId;
        }
        if ($articleId !== null && $articleId > 0) {
            $updates[] = 'ArticleID = ?';
            $params[] = $articleId;
        }
        if ($quantity !== null && $quantity > 0) {
            $updates[] = 'Quantity = ?';
            $params[] = $quantity;
        }
        if ($unit !== null && $unit !== '') {
            $updates[] = 'Unit = ?';
            $params[] = $unit;
        }
        if ($inputType !== null && in_array($inputType, ['part', 'resource', 'consumable'])) {
            $updates[] = 'InputType = ?';
            $params[] = $inputType;
        }
        if (empty($updates)) {
            return false;
        }
        $params[] = $inputId;
        try {
            $stmt = $this->pdo->prepare("
                UPDATE $this->tableName
                SET " . implode(', ', $updates) . "
                WHERE InputID = ?
            ");
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteInput(int $inputId): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM $this->tableName WHERE InputID = ?");
            return $stmt->execute([$inputId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function listInputs(): array
    {
        try {
            $stmt = $this->pdo->query("
                SELECT InputID, RecipeID, ArticleID, Quantity, Unit, InputType, CreatedAt, UpdatedAt
                FROM $this->tableName
                ORDER BY RecipeID ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}

class RecipeOutputManager
{
    private $pdo;
    private $tableName = "recipe_outputs";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createOutput(int $recipeId, int $articleId, float $quantity = 1.0, string $unit = 'unit', bool $isPrimary = true): bool
    {
        if ($recipeId <= 0 || $articleId <= 0 || $quantity <= 0) {
            return false;
        }
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO $this->tableName (RecipeID, ArticleID, Quantity, Unit, IsPrimary)
                VALUES (?, ?, ?, ?, ?)
            ");
            return $stmt->execute([$recipeId, $articleId, $quantity, $unit, $isPrimary ? 1 : 0]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getOutputById(int $outputId): ?array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT OutputID, RecipeID, ArticleID, Quantity, Unit, IsPrimary, CreatedAt, UpdatedAt
                FROM $this->tableName
                WHERE OutputID = ?
            ");
            $stmt->execute([$outputId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function updateOutput(int $outputId, ?int $recipeId = null, ?int $articleId = null, ?float $quantity = null, ?string $unit = null, ?bool $isPrimary = null): bool
    {
        $updates = [];
        $params = [];
        if ($recipeId !== null && $recipeId > 0) {
            $updates[] = 'RecipeID = ?';
            $params[] = $recipeId;
        }
        if ($articleId !== null && $articleId > 0) {
            $updates[] = 'ArticleID = ?';
            $params[] = $articleId;
        }
        if ($quantity !== null && $quantity > 0) {
            $updates[] = 'Quantity = ?';
            $params[] = $quantity;
        }
        if ($unit !== null && $unit !== '') {
            $updates[] = 'Unit = ?';
            $params[] = $unit;
        }
        if ($isPrimary !== null) {
            $updates[] = 'IsPrimary = ?';
            $params[] = $isPrimary ? 1 : 0;
        }
        if (empty($updates)) {
            return false;
        }
        $params[] = $outputId;
        try {
            $stmt = $this->pdo->prepare("
                UPDATE $this->tableName
                SET " . implode(', ', $updates) . "
                WHERE OutputID = ?
            ");
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteOutput(int $outputId): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM $this->tableName WHERE OutputID = ?");
            return $stmt->execute([$outputId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function listOutputs(): array
    {
        try {
            $stmt = $this->pdo->query("
                SELECT OutputID, RecipeID, ArticleID, Quantity, Unit, IsPrimary, CreatedAt, UpdatedAt
                FROM $this->tableName
                ORDER BY RecipeID ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>