<?php

// A simple test framework
$passed = 0;
$failed = 0;

require_once __DIR__ . '/Mocks.php';

$testFiles = glob(__DIR__ . '/*Test.php');

foreach ($testFiles as $file) {
    require_once $file;
    $className = basename($file, '.php');

    if (class_exists($className)) {
        $methods = get_class_methods($className);

        foreach ($methods as $method) {
            if (strpos($method, 'test') === 0) {
                // Instantiate a new test object for EACH test method to ensure isolation
                $testObject = new $className();
                try {
                    $testObject->$method();
                    echo "✓ $className::$method\n";
                    $passed++;
                } catch (Exception $e) {
                    echo "✗ $className::$method - " . $e->getMessage() . "\n";
                    $failed++;
                }
            }
        }
    }
}

echo "\nTests completed. Passed: $passed, Failed: $failed\n";
if ($failed > 0) {
    exit(1);
}
