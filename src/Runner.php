<?php

namespace Lucinda\UnitTest;

use Lucinda\UnitTest\Runner\ClassInfo;
use Lucinda\UnitTest\Runner\ClassesFinder;
use Lucinda\UnitTest\Runner\UnitTest;
use Lucinda\UnitTest\Validator\SQL;

/**
 * Locates and runs unit tests, expecting children to display results
 */
class Runner
{
    /**
     * @var UnitTest[]
     */
    private array $results = [];

    /**
     * UnitTest runner constructor.
     *
     * @param  Configuration $configuration
     * @throws Exception
     */
    public function __construct(Configuration $configuration)
    {
        if ($configuration->getSQLDataSource()) {
            SQL::setDataSource($configuration->getSQLDataSource());
        }
        $apis = $configuration->getAPIs();
        foreach ($apis as $api) {
            $sourcesFinder = new ClassesFinder($api->getSourcesPath());
            $testsFinder = new ClassesFinder($api->getTestsPath());
            $this->setResults($sourcesFinder->getResults(), $testsFinder->getResults());
        }
    }

    /**
     * Matches sources to tests, executes latter and collects results
     *
     * @param array<string,ClassInfo> $sourceFiles List of source classes/files found along with adjacent info.
     * @param array<string,ClassInfo> $testFiles   List of test classes/files found along with adjacent info.
     */
    private function setResults(array $sourceFiles, array $testFiles): void
    {
        foreach ($sourceFiles as $infoSrc) {
            if ($infoSrc->isAbstract || $infoSrc->isInterface || $infoSrc->isEnum) {
                continue;
            }
            $srcClassName = $infoSrc->className;
            $testClassName = $srcClassName."Test";
            $srcNamespace = $infoSrc->namespace;
            $testNamespace = ($srcNamespace ? "Test\\".$srcNamespace : "");
            $testClassNameNamespaced = ($testNamespace ? $testNamespace."\\" : "").$testClassName;
            // check if class is covered
            if (!isset($testFiles[$testClassNameNamespaced])) {
                $this->results[] = $this->getFailedUnitTest(
                    $testClassNameNamespaced,
                    "Class not covered by unit tests"
                );
            } elseif ($testNamespace!=$testFiles[$testClassNameNamespaced]->namespace) {
                $this->results[] = $this->getFailedUnitTest(
                    $testClassNameNamespaced,
                    "Unit test namespace doesn't match namespace of class"
                );
            } else {
                $this->runAllClassMethods($testClassNameNamespaced, $infoSrc->methods, $testFiles);
            }
        }
    }

    /**
     * Instance unit test class and execute all public methods
     *
     * @param  string                  $testClassNameNamespaced
     * @param  array<string,string>    $methods
     * @param  array<string,ClassInfo> $testFiles
     * @return void
     */
    private function runAllClassMethods(
        string $testClassNameNamespaced,
        array $methods,
        array $testFiles
    ): void {
        $testObject = new $testClassNameNamespaced();
        foreach ($methods as $method) {
            if (!isset($testFiles[$testClassNameNamespaced]->methods[$method])) {
                $this->results[] = $this->getFailedUnitTest(
                    $testClassNameNamespaced,
                    "Unit test namespace doesn't match namespace of class",
                    $method
                );
            } else {
                $result = $testObject->{$method}();
                $this->parseResults($result, $testClassNameNamespaced, $method);
            }
        }
        foreach ($testFiles[$testClassNameNamespaced]->methods as $method) {
            if (!isset($methods[$method])) {
                $result = $testObject->{$method}();
                $this->parseResults($result, $testClassNameNamespaced, $method);
            }
        }
    }

    /**
     * Parses method execution results of unit tests
     *
     * @param mixed  $result
     * @param string $testClassNameNamespaced
     * @param string $method
     */
    private function parseResults(mixed $result, string $testClassNameNamespaced, string $method): void
    {
        if (is_array($result) && !empty($result)) {
            foreach ($result as $object) {
                if ($object instanceof Result) {
                    $unitTest = new UnitTest();
                    $unitTest->className = $testClassNameNamespaced;
                    $unitTest->methodName = $method;
                    $unitTest->result = $object;
                    $this->results[] = $unitTest;
                } else {
                    $this->results[] = $this->getFailedUnitTest(
                        $testClassNameNamespaced,
                        "Invalid unit test response",
                        $method
                    );
                }
            }
        } elseif ($result instanceof Result) {
            $unitTest = new UnitTest();
            $unitTest->className = $testClassNameNamespaced;
            $unitTest->methodName = $method;
            $unitTest->result = $result;
            $this->results[] = $unitTest;
        } else {
            $this->results[] = $this->getFailedUnitTest(
                $testClassNameNamespaced,
                "Invalid unit test response",
                $method
            );
        }
    }

    /**
     * @param  string $testClassNameNamespaced
     * @param  string $message
     * @param  string $method
     * @return UnitTest
     */
    private function getFailedUnitTest(string $testClassNameNamespaced, string $message, string $method = ""): UnitTest
    {
        $unitTest = new UnitTest();
        $unitTest->className = $testClassNameNamespaced;
        if ($method) {
            $unitTest->methodName = $method;
        }
        $unitTest->result = new Result(false, $message);
        return $unitTest;
    }

    /**
     * Gets results of unit tests
     *
     * @return UnitTest[]
     */
    public function getResults(): array
    {
        return $this->results;
    }
}
