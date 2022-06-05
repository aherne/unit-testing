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
     * @param  Configuration $configuration.
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
     * @param  array<string,ClassInfo> $sourceFiles List of source classes/files found along with adjacent info.
     * @param  array<string,ClassInfo> $testFiles   List of test classes/files found along with adjacent info.
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
            $testNamespace = ($srcNamespace ? "Test\\".$srcNamespace : "");
            $testFileLocation = str_replace(
                [$this->sourcesFolder, ".php"],
                [$this->testsFolder, "Test.php"],
                $infoSrc->filePath
            );
            $testClassNameNamespaced = ($testNamespace ? $testNamespace."\\" : "").$testClassName;
            // check if class is covered
            if (!isset($testFiles[$testClassNameNamespaced])) {
                $this->createClass(
                    $testClassName,
                    $testNamespace,
                    $testFileLocation,
                    $infoSrc->methods
                );
            } elseif ($testNamespace!=$testFiles[$testClassNameNamespaced]->namespace) {
                throw new Exception("Class ".$testClassName." should belong to namespace ".$testNamespace);
            } else {
                $this->updateClass(
                    $testClassNameNamespaced,
                    $testClassName,
                    $testNamespace,
                    $testFileLocation,
                    $infoSrc->methods,
                    $testFiles
                );
            }
        }
    }

    /**
     * Creates unit test class automatically
     *
     * @param  string   $testClassName
     * @param  string   $testNamespace
     * @param  string   $testFileLocation
     * @param  string[] $methods
     * @return void
     */
    private function createClass(
        string $testClassName,
        string $testNamespace,
        string $testFileLocation,
        array $methods
    ): void {
        $test = new TestClass($testClassName, $testNamespace, $testFileLocation);
        $test->create($methods);
    }

    /**
     * Adds missing methods to be covered in existing unit tests classes
     *
     * @param  string                  $testClassNameNamespaced
     * @param  string                  $testClassName
     * @param  string                  $testNamespace
     * @param  string                  $testFileLocation
     * @param  string[]                $methods
     * @param  array<string,ClassInfo> $testFiles
     * @return void
     */
    private function updateClass(
        string $testClassNameNamespaced,
        string $testClassName,
        string $testNamespace,
        string $testFileLocation,
        array $methods,
        array $testFiles
    ): void {
        $methodsMissing = [];
        foreach ($methods as $method) {
            if (!isset($testFiles[$testClassNameNamespaced]->methods[$method])) {
                $methodsMissing[] = $method;
            }
        }
        if (!empty($methodsMissing)) {
            $test = new TestClass($testClassName, $testNamespace, $testFileLocation);
            $test->update($methodsMissing);
        }
    }
}
