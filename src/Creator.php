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
    private string $sourcesFolder = "";
    private string $testsFolder = "";
    
    /**
     * UnitTest creator constructor.
     *
     * @param Configuration $configuration.
     * @throws Exception
     */
    public function __construct(Configuration $configuration)
    {
        $apis = $configuration->getAPIs();
        foreach ($apis as $api) {
            $this->sourcesFolder = $api->getSourcesPath();
            $this->testsFolder = $api->getTestsPath();
            $sourcesFinder = new ClassesFinder($this->sourcesFolder);
            $testsFinder = new ClassesFinder($this->testsFolder);
            $this->execute($sourcesFinder->getResults(), $testsFinder->getResults());
        }
    }
    
    /**
     * Matches sources to tests and creates tests if not present
     *
     * @param ClassInfo[string] $sourceFiles List of source classes/files found along with adjacent info.
     * @param ClassInfo[string] $testFiles List of test classes/files found along with adjacent info.
     * @throws Exception
     */
    private function execute(array $sourceFiles, array $testFiles): void
    {
        foreach ($sourceFiles as $infoSrc) {
            if ($infoSrc->isAbstract || $infoSrc->isInterface || $infoSrc->isEnum) {
                continue;
            }
            $srcClassName = $infoSrc->className;
            $testClassName = $srcClassName."Test";
            $srcNamespace = $infoSrc->namespace;
            $testNamespace = ($srcNamespace?"Test\\".$srcNamespace:"");
            $testFileLocation = str_replace([$this->sourcesFolder, ".php"], [$this->testsFolder, "Test.php"], $infoSrc->filePath);
            $testClassNameWithNamespace = ($testNamespace?$testNamespace."\\":"").$testClassName;
            // check if class is covered
            if (!isset($testFiles[$testClassNameWithNamespace])) {
                $test = new TestClass($testClassName, $testNamespace, $testFileLocation);
                $test->create($infoSrc->methods);
            } elseif ($testNamespace!=$testFiles[$testClassNameWithNamespace]->namespace) {
                throw new Exception("Class ".$testClassName." should belong to namespace ".$testNamespace);
            } else {
                $methods = [];
                foreach ($infoSrc->methods as $method) {
                    if (!isset($testFiles[$testClassNameWithNamespace]->methods[$method])) {
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
