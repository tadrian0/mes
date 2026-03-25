<?php
require_once __DIR__ . '/Mocks.php';

class Assert {
    public static function assertTrue($condition, $message = '') {
        if ($condition !== true) {
            throw new Exception("Assertion failed: Expected true, got " . var_export($condition, true) . ". $message");
        }
    }

    public static function assertFalse($condition, $message = '') {
        if ($condition !== false) {
            throw new Exception("Assertion failed: Expected false, got " . var_export($condition, true) . ". $message");
        }
    }

    public static function assertSame($expected, $actual, $message = '') {
        if ($expected !== $actual) {
            throw new Exception("Assertion failed: Expected " . var_export($expected, true) . ", got " . var_export($actual, true) . ". $message");
        }
    }

    public static function assertStringContainsString($needle, $haystack, $message = '') {
        if (strpos($haystack, $needle) === false) {
            throw new Exception("Assertion failed: Expected string to contain '$needle', but it did not. Actual string: '$haystack'. $message");
        }
    }
}

function runTests() {
    $tests = glob(__DIR__ . '/*Test.php');
    $pass = 0;
    $fail = 0;

    foreach ($tests as $file) {
        require_once $file;
        $className = basename($file, '.php');
        if (class_exists($className)) {
            $testObj = new $className();
            $methods = get_class_methods($testObj);
            foreach ($methods as $method) {
                if (str_starts_with($method, 'test')) {
                    try {
                        $testObj->$method();
                        echo "PASS: $className::$method\n";
                        $pass++;
                    } catch (Exception $e) {
                        echo "FAIL: $className::$method\n";
                        echo "      " . $e->getMessage() . "\n";
                        $fail++;
                    } catch (Error $e) {
                        echo "FAIL (Error): $className::$method\n";
                        echo "      " . $e->getMessage() . "\n";
                        $fail++;
                    }
                }
            }
        }
    }

    echo "\nTotal: " . ($pass + $fail) . " tests\n";
    echo "Pass: $pass\n";
    echo "Fail: $fail\n";

    if ($fail > 0) {
        exit(1);
    }
}

runTests();
