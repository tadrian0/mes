<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/mes/includes/Config.php';
require_once INCLUDE_PATH . 'Database.php';
//require_once INCLUDE_PATH . 'ApiAuth.php';

header('Content-Type: application/json');

$country = isset($_GET['country']) && $_GET['country'] !== '' ? $_GET['country'] : null;
$city    = isset($_GET['city']) && $_GET['city'] !== '' ? $_GET['city'] : null;
$plant   = isset($_GET['plant']) && $_GET['plant'] !== '' ? $_GET['plant'] : null;

$response = [
    'cities' => [],
    'plants' => [],
    'sections' => []
];

try {
    $sqlCity = "SELECT DISTINCT c.Name FROM city c 
                JOIN country co ON c.CountryID = co.CountryID 
                WHERE 1=1";
    $paramsCity = [];
    if ($country) {
        $sqlCity .= " AND co.Name = ?";
        $paramsCity[] = $country;
    }
    $sqlCity .= " ORDER BY c.Name ASC";
    $stmt = $pdo->prepare($sqlCity);
    $stmt->execute($paramsCity);
    $response['cities'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $sqlPlant = "SELECT DISTINCT p.Name FROM plant p
                 JOIN city c ON p.CityID = c.CityID
                 JOIN country co ON c.CountryID = co.CountryID
                 WHERE 1=1";
    $paramsPlant = [];
    if ($country) {
        $sqlPlant .= " AND co.Name = ?";
        $paramsPlant[] = $country;
    }
    if ($city) {
        $sqlPlant .= " AND c.Name = ?";
        $paramsPlant[] = $city;
    }
    $sqlPlant .= " ORDER BY p.Name ASC";
    $stmt = $pdo->prepare($sqlPlant);
    $stmt->execute($paramsPlant);
    $response['plants'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $sqlSection = "SELECT DISTINCT s.Name FROM section s
                   JOIN plant p ON s.PlantID = p.PlantID
                   JOIN city c ON p.CityID = c.CityID
                   JOIN country co ON c.CountryID = co.CountryID
                   WHERE 1=1";
    $paramsSection = [];
    if ($country) {
        $sqlSection .= " AND co.Name = ?";
        $paramsSection[] = $country;
    }
    if ($city) {
        $sqlSection .= " AND c.Name = ?";
        $paramsSection[] = $city;
    }
    if ($plant) {
        $sqlSection .= " AND p.Name = ?";
        $paramsSection[] = $plant;
    }
    $sqlSection .= " ORDER BY s.Name ASC";
    $stmt = $pdo->prepare($sqlSection);
    $stmt->execute($paramsSection);
    $response['sections'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>