<?php
require_once("../../includes/Database.php");
function CreateMaterial(string $name, ?string $description = null, ?string $imagePath = null, string $qualityControl = 'Pending'): bool
{
    if (empty($name)) {
        return false;
    }
    $tableName = "article";
    try {
        $stmt = $pdo->prepare("
                INSERT INTO $tableName (Name, Description, ImagePath, QualityControl)
                VALUES (?, ?, ?, ?)
            ");
        return $stmt->execute([$name, $description, $imagePath, $qualityControl]);
    } catch (PDOException $e) {
        return false;
    }
}
?>