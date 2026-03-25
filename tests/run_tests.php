<?php
// Simple test runner to discover and run test functions
$testFiles = glob(__DIR__ . '/*Test.php');

$totalTests = 0;
$passedTests = 0;

foreach ($testFiles as $file) {
    require_once $file;
}

$functions = get_defined_functions()['user'];
foreach ($functions as $function) {
    if (strpos($function, 'test') === 0) {
        $totalTests++;
        try {
            if ($function() !== false) {
                $passedTests++;
            }
        } catch (Throwable $e) {
            echo "FAIL: $function threw an exception: " . $e->getMessage() . "\n";
        }
    }
}

echo "\nTests run: $totalTests, Passed: $passedTests, Failed: " . ($totalTests - $passedTests) . "\n";
if ($passedTests !== $totalTests || $totalTests === 0) {
    exit(1);
}
