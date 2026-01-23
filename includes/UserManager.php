<?php
class UserManager
{
    private $pdo;
    private $tableName = "user";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Create a new user
     */
    public function createUser(string $username, string $password, string $role): bool
    {
        try {
            $check = $this->pdo->prepare("SELECT COUNT(*) FROM $this->tableName WHERE OperatorUsername = ?");
            $check->execute([$username]);
            if ($check->fetchColumn() > 0) {
                return false; 
            }

            $stmt = $this->pdo->prepare("
                INSERT INTO $this->tableName (OperatorUsername, OperatorPassword, OperatorRoles) 
                VALUES (?, ?, ?)
            ");
            
            return $stmt->execute([$username, $password, $role]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Update existing user
     */
    public function updateUser(int $id, string $username, ?string $password, string $role): bool
    {
        try {
            if ($password) {
                $stmt = $this->pdo->prepare("
                    UPDATE $this->tableName 
                    SET OperatorUsername = ?, OperatorPassword = ?, OperatorRoles = ? 
                    WHERE OperatorID = ?
                ");
                return $stmt->execute([$username, $password, $role, $id]);
            } else {
                // Update without changing password
                $stmt = $this->pdo->prepare("
                    UPDATE $this->tableName 
                    SET OperatorUsername = ?, OperatorRoles = ? 
                    WHERE OperatorID = ?
                ");
                return $stmt->execute([$username, $role, $id]);
            }
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Delete user
     */
    public function deleteUser(int $id): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM $this->tableName WHERE OperatorID = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * List users with optional filters
     */
    public function listUsers(?string $search = null, ?string $role = null): array
    {
        try {
            $sql = "SELECT * FROM $this->tableName WHERE 1=1";
            $params = [];

            if (!empty($search)) {
                $sql .= " AND OperatorUsername LIKE ?";
                $params[] = "%$search%";
            }

            if (!empty($role)) {
                $sql .= " AND OperatorRoles = ?";
                $params[] = $role;
            }

            $sql .= " ORDER BY OperatorUsername ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getUserById(int $id): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM $this->tableName WHERE OperatorID = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }
}
?>