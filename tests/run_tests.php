<?php
require_once __DIR__ . '/Mocks.php';

$testFiles = glob(__DIR__ . '/*Test.php');

$failed = 0;
$passed = 0;

foreach ($testFiles as $file) {
    require_once $file;
    $className = basename($file, '.php');
    $obj = new $className();
    $methods = get_class_methods($obj);
    foreach ($methods as $method) {
        if (strpos($method, 'test') === 0) {
            try {
                $obj->$method();
                echo "Passed: $className::$method\n";
                $passed++;
            } catch (Exception $e) {
                echo "Failed: $className::$method - " . $e->getMessage() . "\n";
                $failed++;
            } catch (Error $e) {
                echo "Failed: $className::$method - " . $e->getMessage() . "\n";
                $failed++;
            }
        }
    }
}

echo "\nResults: $passed passed, $failed failed.\n";
exit($failed > 0 ? 1 : 0);
