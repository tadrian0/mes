<?php
$siteBaseUrl = "https://localhost/mes/";
define('INCLUDE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/mes/includes/');

define('ALLOWED_ORIGINS', [
    'http://localhost',
    'https://localhost',
    'http://localhost:3000',
    'http://localhost:4200',
    'http://localhost:5173'
]);
?>