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
     * Securely verifies a password against a stored hash (with legacy plaintext fallback).
     */
    public static function verifyPassword(string $inputPassword, string $storedHash): bool
    {
        // First try standard password_verify (for bcrypt/argon2 hashes)
        if (password_verify($inputPassword, $storedHash)) {
            return true;
        }

        // Check if the stored hash doesn't look like a standard PHP hash
        $info = password_get_info($storedHash);
        if ($info['algoName'] === 'unknown') {
            // Fallback for legacy plaintext passwords (using hash_equals to mitigate timing attacks)
            return hash_equals($storedHash, $inputPassword);
        }

        return false;
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

            // Hash the password securely
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $this->pdo->prepare("
                INSERT INTO $this->tableName (OperatorUsername, OperatorPassword, OperatorRoles) 
                VALUES (?, ?, ?)
            ");
            
            return $stmt->execute([$username, $hashedPassword, $role]);
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
                // Hash the new password securely
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $this->pdo->prepare("
                    UPDATE $this->tableName 
                    SET OperatorUsername = ?, OperatorPassword = ?, OperatorRoles = ? 
                    WHERE OperatorID = ?
                ");
                return $stmt->execute([$username, $hashedPassword, $role, $id]);
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

    /**
     * Get user details by Username (Used for Login)
     */
    public function getUserByUsername(string $username): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM $this->tableName WHERE OperatorUsername = ?");
            $stmt->execute([$username]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Helper to get just the username string by ID
     */
    public function getUsernameById(int $id): ?string
    {
        try {
            $stmt = $this->pdo->prepare("SELECT OperatorUsername FROM $this->tableName WHERE OperatorID = ?");
            $stmt->execute([$id]);
            return $stmt->fetchColumn() ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }
}
?>