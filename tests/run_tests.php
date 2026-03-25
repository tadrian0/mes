<?php
// Custom test runner
$testsDir = __DIR__;
$files = scandir($testsDir);

$passed = 0;
$failed = 0;

echo "Running tests...\n";

foreach ($files as $file) {
    if (strpos($file, 'Test.php') !== false) {
        require_once "$testsDir/$file";
        $className = basename($file, '.php');
        if (class_exists($className)) {
            $testObj = new $className();
            $methods = get_class_methods($testObj);
            foreach ($methods as $method) {
                if (strpos($method, 'test') === 0) {
                    try {
                        $testObj->$method();
                        echo "  [PASS] $className::$method\n";
                        $passed++;
                    } catch (Exception $e) {
                        echo "  [FAIL] $className::$method - " . $e->getMessage() . "\n";
                        $failed++;
                    }
                }
            }
        }
    }
}

echo "\nSummary: $passed passed, $failed failed.\n";
if ($failed > 0) {
    exit(1);
}
