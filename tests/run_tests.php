<?php

echo "Starting tests...\n";

require_once __DIR__ . '/ArticleManagerTest.php';

$testsPassed = 0;
$testsFailed = 0;

try {
    $articleManagerTest = new ArticleManagerTest();
    $articleManagerTest->run();
    $testsPassed++;
} catch (Exception $e) {
    echo "ArticleManagerTest failed: " . $e->getMessage() . "\n";
    $testsFailed++;
}

echo "\nTest Summary:\n";
echo "Passed: $testsPassed\n";
echo "Failed: $testsFailed\n";

if ($testsFailed > 0) {
    exit(1);
} else {
    exit(0);
}
