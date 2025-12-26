<?php
class ApiKeyManager
{
    private $pdo;
    private $keyTable = "api_keys";
    private $auditTable = "api_audit_log";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Generates a new API Key
     */
    public function createKey(int $userId, string $name, string $permissions = 'ALL', string $scope = 'ALL'): ?string
    {
        $keyString = bin2hex(random_bytes(16)); 

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("
                INSERT INTO $this->keyTable (KeyString, Name, UserID, Permissions, ScopePlants)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$keyString, $name, $userId, $permissions, $scope]);
            $keyId = $this->pdo->lastInsertId();

            $this->logEvent($keyId, $userId, 'Created', null, "Generated via Login/Admin");

            $this->pdo->commit();
            return $keyString;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return null;
        }
    }

    /**
     * Validates a key and logs usage
     */
    public function validateAndLog(string $keyString, string $endpoint): bool
    {
        $stmt = $this->pdo->prepare("SELECT KeyID, IsActive FROM $this->keyTable WHERE KeyString = ?");
        $stmt->execute([$keyString]);
        $key = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$key || !$key['IsActive']) {
            return false;
        }

        $upd = $this->pdo->prepare("UPDATE $this->keyTable SET LastUsedAt = NOW() WHERE KeyID = ?");
        $upd->execute([$key['KeyID']]);

        $this->logEvent($key['KeyID'], null, 'Used', $endpoint, null);

        return true;
    }

    /**
     * Helper to log events
     */
    public function logEvent(?int $keyId, ?int $userId, string $action, ?string $endpoint = null, ?string $details = null)
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $stmt = $this->pdo->prepare("
            INSERT INTO $this->auditTable (KeyID, UserID, Action, Endpoint, IPAddress, Details)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$keyId, $userId, $action, $endpoint, $ip, $details]);
    }

    public function revokeKey(int $keyId, int $adminId): bool
    {
        $this->logEvent($keyId, $adminId, 'Deleted', null, 'Key revoked by admin');
        $stmt = $this->pdo->prepare("DELETE FROM $this->keyTable WHERE KeyID = ?");
        return $stmt->execute([$keyId]);
    }

    public function listKeys(?int $userId = null): array
    {
        $sql = "SELECT k.*, u.OperatorUsername 
                FROM $this->keyTable k 
                JOIN user u ON k.UserID = u.OperatorID 
                WHERE 1=1";
        $params = [];
        if ($userId) {
            $sql .= " AND k.UserID = ?";
            $params[] = $userId;
        }
        $sql .= " ORDER BY k.CreatedAt DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listAuditLogs(?int $keyId = null, ?string $action = null): array
    {
        $sql = "SELECT a.*, k.Name as KeyName, u.OperatorUsername 
                FROM $this->auditTable a 
                LEFT JOIN $this->keyTable k ON a.KeyID = k.KeyID
                LEFT JOIN user u ON a.UserID = u.OperatorID
                WHERE 1=1";
        $params = [];
        if ($keyId) { $sql .= " AND a.KeyID = ?"; $params[] = $keyId; }
        if ($action) { $sql .= " AND a.Action = ?"; $params[] = $action; }
        
        $sql .= " ORDER BY a.Timestamp DESC LIMIT 500"; 
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>