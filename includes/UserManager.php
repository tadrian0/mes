<?php
class UserManager
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createUser(string $username, string $password, string $roles = 'operator'): bool
    {
        if (empty($username) || empty($password)) {
            return false;
        }
        try {
            $stmt = $this->pdo->prepare('
                INSERT INTO Users (OperatorUsername, OperatorPassword, OperatorRoles)
                VALUES (?, ?, ?)
            ');
            return $stmt->execute([$username, $password, $roles]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getUserById(int $userId): ?array
    {
        try {
            $stmt = $this->pdo->prepare('
                SELECT OperatorID, OperatorUsername, OperatorRoles, CreatedAt, UpdatedAt
                FROM Users
                WHERE OperatorID = ?
            ');
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function getUserByUsername(string $username): ?array
    {
        try {
            $stmt = $this->pdo->prepare('
                SELECT OperatorID, OperatorUsername, OperatorRoles, CreatedAt, UpdatedAt
                FROM Users
                WHERE OperatorUsername = ?
            ');
            $stmt->execute([$username]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function updateUser(int $userId, ?string $username = null, ?string $password = null, ?string $roles = null): bool
    {
        $updates = [];
        $params = [];
        if ($username !== null && $username !== '') {
            $updates[] = 'OperatorUsername = ?';
            $params[] = $username;
        }
        if ($password !== null && $password !== '') {
            $updates[] = 'OperatorPassword = ?';
            $params[] = $password;
        }
        if ($roles !== null && $roles !== '') {
            $updates[] = 'OperatorRoles = ?';
            $params[] = $roles;
        }
        if (empty($updates)) {
            return false;
        }
        $params[] = $userId;
        try {
            $stmt = $this->pdo->prepare('
                UPDATE Users
                SET ' . implode(', ', $updates) . '
                WHERE OperatorID = ?
            ');
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteUser(int $userId): bool
    {
        try {
            $stmt = $this->pdo->prepare('DELETE FROM Users WHERE OperatorID = ?');
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function listUsers(): array
    {
        try {
            $stmt = $this->pdo->query('
                SELECT OperatorID, OperatorUsername, OperatorRoles, CreatedAt, UpdatedAt
                FROM Users
                ORDER BY OperatorUsername ASC
            ');
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>