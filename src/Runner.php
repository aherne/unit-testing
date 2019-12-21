<?php
namespace Lucinda\UnitTest;

use Lucinda\UnitTest\Runner\ClassInfo;
use Lucinda\UnitTest\Runner\ClassesFinder;
use Lucinda\UnitTest\Runner\UnitTest;

/**
 * Locates and runs unit tests, expecting children to display results
 */
abstract class Runner
{
    /**
     * UnitTest runner constructor.
     *
     * @param string $libraryFolder Folder on disk in which library is located at.
     * @param string $sourcesFolder Path to sources files folder relative to library folder.
     * @param string $testsFolder Path to unit tests folder relative to library folder.
     * @throws Exception
     */
    public function __construct(string $libraryFolder, string $sourcesFolder="src", string $testsFolder="tests")
    {
        $sourcesFinder = new ClassesFinder($libraryFolder."/".$sourcesFolder);
        $testsFinder = new ClassesFinder($libraryFolder."/".$testsFolder);
        $results = $this->execute($sourcesFinder->getResults(), $testsFinder->getResults());
        $this->display($results);
    }

    /**
     * Matches sources to tests and executes latter
     *
     * @param ClassInfo[string] $sourceFiles List of source classes/files found along with adjacent info.
     * @param ClassInfo[string] $testFiles List of test classes/files found along with adjacent info.
     * @return UnitTest[] List of unit test results
     * @throws Exception
     */
    private function execute(array $sourceFiles, array $testFiles): array
    {
        $output = [];
        foreach ($sourceFiles as $infoSrc) {
            $srcClassName = $infoSrc->className;
            $testClassName = $srcClassName."Test";
            $srcNamespace = $infoSrc->namespace;
            $testNamespace = ($srcNamespace?"\\Test\\".$srcNamespace:"");
            $testClassNameWithNamespace = ($testNamespace?$testNamespace."\\":"").$testClassName;
            // check if class is covered
            if (!isset($testFiles[$testClassName])) {
                $unitTest = new UnitTest();
                $unitTest->className = $testClassNameWithNamespace;
                $unitTest->result = new Result(false, "Class not covered by unit test");
                $output[] = $unitTest;
            } elseif ($testNamespace!=$testFiles[$testClassName]->namespace) {
                $unitTest = new UnitTest();
                $unitTest->className = $testClassNameWithNamespace;
                $unitTest->result = new Result(false, "Unit test namespace doesn't match namespace of class");
                $output[] = $unitTest;
            } else {
                $testObject = new $testClassNameWithNamespace();
                foreach ($infoSrc->methods as $method) {
                    if (!isset($testFiles[$testClassName]->methods[$method])) {
                        $unitTest = new UnitTest();
                        $unitTest->className = $testClassNameWithNamespace;
                        $unitTest->methodName = $method;
                        $unitTest->result = new Result(false, "Method not covered by unit test");
                        $output[] = $unitTest;
                    } else {
                        $result = $testObject->{$method}();
                        if (is_array($result) && !empty($result)) {
                            foreach ($result as $r) {
                                if ($r instanceof Result) {
                                    $unitTest = new UnitTest();
                                    $unitTest->className = $testClassNameWithNamespace;
                                    $unitTest->methodName = $method;
                                    $unitTest->result = $r;
                                    $output[] = $unitTest;
                                } else {
                                    $unitTest = new UnitTest();
                                    $unitTest->className = $testClassNameWithNamespace;
                                    $unitTest->methodName = $method;
                                    $unitTest->result = new Result(false, "Invalid unit test response");
                                    $output[] = $unitTest;
                                }
                            }
                        } elseif ($result instanceof Result) {
                            $unitTest = new UnitTest();
                            $unitTest->className = $testClassNameWithNamespace;
                            $unitTest->methodName = $method;
                            $unitTest->result = $result;
                            $output[] = $unitTest;
                        } else {
                            $unitTest = new UnitTest();
                            $unitTest->className = $testClassNameWithNamespace;
                            $unitTest->methodName = $method;
                            $unitTest->result = new Result(false, "Invalid unit test response");
                            $output[] = $unitTest;
                        }
                    }
                }
            }
        }
        return $output;
    }

    /**
     * Displays results of unit tests
     *
     * @param UnitTest[] $results
     */
    abstract protected function display(array $results): void;
}
