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
    private $results = [];
    
    /**
     * UnitTest runner constructor.
     *
     * @param Configuration $configuration
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
     * @param ClassInfo[string] $sourceFiles List of source classes/files found along with adjacent info.
     * @param ClassInfo[string] $testFiles List of test classes/files found along with adjacent info.
     * @throws Exception
     */
    private function setResults(array $sourceFiles, array $testFiles): void
    {
        foreach ($sourceFiles as $infoSrc) {
            if ($infoSrc->isAbstract || $infoSrc->isInterface) {
                continue;
            }
            $srcClassName = $infoSrc->className;
            $testClassName = $srcClassName."Test";
            $srcNamespace = $infoSrc->namespace;
            $testNamespace = ($srcNamespace?"Test\\".$srcNamespace:"");
            $testClassNameWithNamespace = ($testNamespace?$testNamespace."\\":"").$testClassName;
            // check if class is covered
            if (!isset($testFiles[$testClassNameWithNamespace])) {
                $unitTest = new UnitTest();
                $unitTest->className = $testClassNameWithNamespace;
                $unitTest->result = new Result(false, "Class not covered by unit test");
                $this->results[] = $unitTest;
            } elseif ($testNamespace!=$testFiles[$testClassNameWithNamespace]->namespace) {
                $unitTest = new UnitTest();
                $unitTest->className = $testClassNameWithNamespace;
                $unitTest->result = new Result(false, "Unit test namespace doesn't match namespace of class");
                $this->results[] = $unitTest;
            } else {
                $testObject = new $testClassNameWithNamespace();
                foreach ($infoSrc->methods as $method) {
                    if (!isset($testFiles[$testClassNameWithNamespace]->methods[$method])) {
                        $unitTest = new UnitTest();
                        $unitTest->className = $testClassNameWithNamespace;
                        $unitTest->methodName = $method;
                        $unitTest->result = new Result(false, "Method not covered by unit test");
                        $this->results[] = $unitTest;
                    } else {
                        $result = $testObject->{$method}();
                        if (is_array($result) && !empty($result)) {
                            foreach ($result as $r) {
                                if ($r instanceof Result) {
                                    $unitTest = new UnitTest();
                                    $unitTest->className = $testClassNameWithNamespace;
                                    $unitTest->methodName = $method;
                                    $unitTest->result = $r;
                                    $this->results[] = $unitTest;
                                } else {
                                    $unitTest = new UnitTest();
                                    $unitTest->className = $testClassNameWithNamespace;
                                    $unitTest->methodName = $method;
                                    $unitTest->result = new Result(false, "Invalid unit test response");
                                    $this->results[] = $unitTest;
                                }
                            }
                        } elseif ($result instanceof Result) {
                            $unitTest = new UnitTest();
                            $unitTest->className = $testClassNameWithNamespace;
                            $unitTest->methodName = $method;
                            $unitTest->result = $result;
                            $this->results[] = $unitTest;
                        } else {
                            $unitTest = new UnitTest();
                            $unitTest->className = $testClassNameWithNamespace;
                            $unitTest->methodName = $method;
                            $unitTest->result = new Result(false, "Invalid unit test response");
                            $this->results[] = $unitTest;
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Gets results of unit tests
     *
     * @return Result[]
     */
    public function getResults(): array
    {
        return $this->results;
    }
}
