<?php
namespace Lucinda\UnitTest;

use Lucinda\UnitTest\Runner\ClassInfo;
use Lucinda\UnitTest\Runner\ClassesFinder;
use Lucinda\UnitTest\Runner\UnitTest;

/**
 * Locates and runs unit tests then expects children to display results
 */
abstract class Runner
{
    /**
     * UnitTestRunner constructor.
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
     * @param array $sourceFiles List of source classes/files found along with adjacent info.
     * @param ClassInfo[string] $testFiles List of test classes/files found along with adjacent info.
     * @return ClassInfo[string] List of unit test results by class and method name.
     * @throws Exception
     */
    private function execute(string $sourceFiles, string $testFiles): array
    {
        $output = [];
        foreach ($sourceFiles as $infoSrc) {
            $srcClassName = $infoSrc->className;
            $testClassName = $srcClassName."Test";
            $testClassNameWithNamespace = "\\".($infoSrc->namespace?$infoSrc->namespace."\\":"").$testClassName;
            // check if class is covered
            if (!isset($testFiles[$testClassName])) {
                $unitTest = new UnitTest();
                $unitTest->className = $testClassNameWithNamespace;
                $unitTest->result = new Result(false, "Class not covered by unit test");
                $output[] = $unitTest;
            } elseif ($infoSrc->namespace!=$testFiles[$testClassName]->namespace) {
                $unitTest = new UnitTest();
                $unitTest->className = $testClassNameWithNamespace;
                $unitTest->result = new Result(false, "Unit test namespace doesn't match namespace of class");
                $output[] = $unitTest;
            } else {
                // load classes
                require_once($infoSrc->fileName);
                require_once($testFiles[$testClassName]->fileName);
                // run tests
                $testObject = new $testClassNameWithNamespace();
                foreach ($infoSrc->methods as $method) {
                    $testMethodName = $method."Test";
                    if (!isset($testFiles[$testClassName]->methods[$testMethodName])) {
                        $unitTest = new UnitTest();
                        $unitTest->className = $testClassNameWithNamespace;
                        $unitTest->methodName = $testMethodName;
                        $unitTest->result = new Result(false, "Method not covered by unit test");
                        $output[] = $unitTest;
                    } else {
                        $result = $testObject->{$testMethodName}();
                        if (is_array($result) && !empty($result)) {
                            foreach ($result as $r) {
                                if ($r instanceof Result) {
                                    $unitTest = new UnitTest();
                                    $unitTest->className = $testClassNameWithNamespace;
                                    $unitTest->methodName = $testMethodName;
                                    $unitTest->result = $r;
                                    $output[] = $unitTest;
                                } else {
                                    $unitTest = new UnitTest();
                                    $unitTest->className = $testClassNameWithNamespace;
                                    $unitTest->methodName = $testMethodName;
                                    $unitTest->result = new Result(false, "Invalid unit test response");
                                    $output[] = $unitTest;
                                }
                            }
                        } elseif ($result instanceof Result) {
                            $unitTest = new UnitTest();
                            $unitTest->className = $testClassNameWithNamespace;
                            $unitTest->methodName = $testMethodName;
                            $unitTest->result = $result;
                            $output[] = $unitTest;
                        } else {
                            $unitTest = new UnitTest();
                            $unitTest->className = $testClassNameWithNamespace;
                            $unitTest->methodName = $testMethodName;
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
    abstract protected function display(array $results);
}
