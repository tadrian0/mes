<?php

require_once __DIR__ . '/Mocks.php';

$files = glob(__DIR__ . '/*Test.php');

$totalTests = 0;
$passedTests = 0;

foreach ($files as $file) {
    require_once $file;

    $className = basename($file, '.php');
    if (!class_exists($className)) {
        continue;
    }

    $class = new ReflectionClass($className);
    $instance = $class->newInstance();

    foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if (strpos($method->getName(), 'test') === 0) {
            $totalTests++;
            echo "Running {$className}::{$method->getName()}... ";
            try {
                $method->invoke($instance);
                $passedTests++;
                echo "PASS\n";
            } catch (Exception $e) {
                echo "FAIL: " . $e->getMessage() . "\n";
            } catch (Error $e) {
                echo "ERROR: " . $e->getMessage() . "\n";
            }
        }
    }
}

echo "\nSummary: $passedTests / $totalTests tests passed.\n";

if ($passedTests !== $totalTests) {
    exit(1);
}
