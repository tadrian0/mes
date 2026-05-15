<?php

$host = "localhost";
$dbname = "xooiduyr_mes";
$username = "xooiduyr_root";
$password = "@crgKvFVGv2TUSh";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}
?>