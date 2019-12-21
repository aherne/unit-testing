<?php
namespace Lucinda\UnitTest;

use Lucinda\UnitTest\Runner\ClassInfo;
use Lucinda\UnitTest\Runner\ClassesFinder;
use Lucinda\UnitTest\Creator\TestClass;

/**
 * Creates unit tests for a library based on sources and tests folder
 */
class Creator
{
    private $sourcesFolder;
    private $testsFolder;
    
    /**
     * UnitTest creator constructor.
     *
     * @param string $libraryFolder Folder on disk in which library is located at.
     * @param string $sourcesFolder Path to sources files folder relative to library folder.
     * @param string $testsFolder Path to unit tests folder relative to library folder.
     * @throws Exception
     */
    public function __construct(string $libraryFolder, string $sourcesFolder="src", string $testsFolder="tests")
    {
        $this->sourcesFolder = $libraryFolder."/".$sourcesFolder;
        $this->testsFolder = $libraryFolder."/".$testsFolder;
        $sourcesFinder = new ClassesFinder($this->sourcesFolder);
        $testsFinder = new ClassesFinder($this->testsFolder);
        $this->execute($sourcesFinder->getResults(), $testsFinder->getResults());
    }
    
    /**
     * Matches sources to tests and creates tests if not present
     *
     * @param ClassInfo[string] $sourceFiles List of source classes/files found along with adjacent info.
     * @param ClassInfo[string] $testFiles List of test classes/files found along with adjacent info.
     * @throws Exception
     */
    private function execute(string $sourceFiles, string $testFiles): void
    {
        foreach ($sourceFiles as $infoSrc) {
            $srcClassName = $infoSrc->className;
            $testClassName = $srcClassName."Test";
            $srcNamespace = $infoSrc->namespace;
            $testNamespace = ($srcNamespace?"\\Test\\".$srcNamespace:"");
            $testFileLocation = str_replace([$this->sourcesFolder, ".php"], [$this->testsFolder, "Test.php"], $infoSrc->fileName);
            // check if class is covered
            if (!isset($testFiles[$testClassName])) {
                $test = new TestClass($testClassName, $testNamespace, $testFileLocation);
                $test->create($infoSrc->methods);
            } elseif ($testNamespace!=$testFiles[$testClassName]->namespace) {
                throw new Exception("Class ".$testClassName." should belong to namespace ".$testNamespace);
            } else {
                $methods = [];
                foreach ($infoSrc->methods as $method) {
                    if (!isset($testFiles[$testClassName]->methods[$method])) {
                        $methods[] = $method;
                    }
                }
                if (!empty($methods)) {
                    $test = new TestClass($testClassName, $testNamespace, $testFileLocation);
                    $test->update($methods);
                }
            }
        }
    }
}

