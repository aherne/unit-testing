<?php

namespace Lucinda\UnitTest\Configuration;

use Lucinda\UnitTest\Exception;

/**
 * Encapsulates information of an API under testing detected based on a 'unit_test' XML tag
 */
class UnitTestedAPI
{
    public const DEFAULT_SOURCES_PATH = "src";
    public const DEFAULT_TESTS_PATH = "tests";

    private string $sourcesPath;
    private string $sourcesNamespace;
    private string $testsPath;
    private string $testsNamespace;

    /**
     * Performs detection process
     *
     * @param  \SimpleXMLElement $unitTest
     * @throws Exception
     */
    public function __construct(\SimpleXMLElement $unitTest)
    {
        $this->setSourcesNamespace($unitTest);
        $this->setSourcesPath($unitTest);
        $this->setTestsNamespace($unitTest);
        $this->setTestsPath($unitTest);
    }

    /**
     * Detects source files base namespace based on attribute 'namespace' of 'sources' tag
     *
     * @param \SimpleXMLElement $unitTest
     *
     * @return void
     * @throws Exception
     */
    private function setSourcesNamespace(\SimpleXMLElement $unitTest): void
    {
        $sourcesNamespace = (string) $unitTest->sources["namespace"];
        if (!$sourcesNamespace) {
            throw new Exception("Attribute 'namespace' not defined or empty for 'sources' tag!");
        }
        $this->sourcesNamespace = $sourcesNamespace;
    }

    /**
     * Gets paths to source files based on attribute 'path' of 'sources' tag
     *
     * @param \SimpleXMLElement $unitTest
     *
     * @return void
     * @throws Exception
     */
    private function setSourcesPath(\SimpleXMLElement $unitTest): void
    {
        $sourcesPath = (string) $unitTest->sources["path"];
        if (!$sourcesPath) {
            $sourcesPath = self::DEFAULT_SOURCES_PATH;
        }
        if (!file_exists($sourcesPath)) {
            throw new Exception("Folder not found on disk: ".$sourcesPath);
        }
        $this->sourcesPath = $sourcesPath;
    }

    /**
     * Detects test files base namespace based on attribute 'namespace' of 'tests' tag
     *
     * @param \SimpleXMLElement $unitTest
     *
     * @return void
     * @throws Exception
     */
    private function setTestsNamespace(\SimpleXMLElement $unitTest): void
    {
        $testsNamespace = (string) $unitTest->tests["namespace"];
        if (!$testsNamespace) {
            throw new Exception("Attribute 'namespace' not defined or empty for 'tests' tag!");
        }
        $this->testsNamespace = $testsNamespace;
    }

    /**
     * Gets paths to test files based on attribute 'path' of 'tests' tag
     *
     * @param \SimpleXMLElement $unitTest
     *
     * @return void
     */
    private function setTestsPath(\SimpleXMLElement $unitTest): void
    {
        $testsPath = (string) $unitTest->tests["path"];
        if (!$testsPath) {
            $testsPath = self::DEFAULT_TESTS_PATH;
        }
        if (!file_exists($testsPath)) {
            mkdir($testsPath, 0777);
        }
        $this->testsPath = $testsPath;
    }

    /**
     * Gets base path to API sources folder.
     *
     * @return string
     */
    public function getSourcesPath(): string
    {
        return $this->sourcesPath;
    }

    /**
     * Gets base namespace of API source classes
     *
     * @return string
     */
    public function getSourcesNamespace(): string
    {
        return $this->sourcesNamespace;
    }

    /**
     * Gets base path to API tests folder.
     *
     * @return string
     */
    public function getTestsPath(): string
    {
        return $this->testsPath;
    }

    /**
     * Gets base namespace of API test classes
     *
     * @return string
     */
    public function getTestsNamespace(): string
    {
        return $this->testsNamespace;
    }
}
