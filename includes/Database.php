<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=mes', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}
?>