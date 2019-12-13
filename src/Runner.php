<?php
namespace Lucinda\UnitTest;

/**
 * Locates all files in library and runs their matching unit tests
 */
class Runner
{
    private $results = [];

    /**
     * UnitTestRunner constructor.
     *
     * @param string $libraryFolder Folder on disk in which library is located at.
     * @param string $sourcesFolder Path to sources files folder relative to library folder.
     * @param string $testsFolder Path to unit tests folder relative to library folder.
     * @param string $sourceClassName Class in sources folder (without namespace) that needs to be tested.
     */
    public function __construct(string $libraryFolder, string $sourcesFolder="src", string $testsFolder="tests", string $sourceClassName="")
    {
        try {
            $sourceFiles = $this->getFiles($libraryFolder."/".$sourcesFolder);
            $testFiles = $this->getFiles($libraryFolder."/".$testsFolder);
            $this->results = $this->execute($sourceFiles, $testFiles, $sourceClassName);
        } catch (\Exception $e) {
            $this->results[__NAMESPACE__."\\".__CLASS__][__METHOD__][] = new Result(false, $e->getMessage());
        }
    }

    /**
     * Finds and reads files in sources/tests folder
     *
     * @param string $directory Folder to look for files into.
     * @return array Array where key is class name and value is class info (namespace, class name, methods, file path)
     * @throws \Exception If folder is not found.
     */
    private function getFiles(string $directory): array
    {
        if (!is_dir($directory)) {
            throw new \Exception("Folder not found: ".$directory);
        }
        $output = [];
        $files = scandir($directory);
        foreach ($files as $file) {
            if (in_array($file, [".", ".."])) {
                continue;
            }
            $path = $directory."/".$file;
            if (is_dir($path)) {
                $temp = $this->getFiles($path);
                foreach ($temp as $fileInfo) {
                    $output[$fileInfo["class"]] = $fileInfo;
                }
            } elseif (strpos($file, ".php")) {
                $info = $this->readFile($path);
                if ($info["class"]) {
                    $output[$info["class"]] = $info;
                }
            }
        }
        return $output;
    }

    /**
     * Reads class file and detects namespace (if any), class name, public methods and file location.
     *
     * @param string $fileName Absolute location of class file.
     * @return array Class info (namespace, class name, methods, file path)
     */
    private function readFile(string $fileName): array
    {
        $content = file_get_contents($fileName);
        preg_match("/(\n\s*namespace\s*([^;]+))/", $content, $m1);
        $namespace = (isset($m1[2])?$m1[2]:"");
        // TODO: support also abstract classes and interfaces in the future
        preg_match("/(\n\s*class\s*([a-zA-Z0-9]+))/", $content, $m2);
        $className = (isset($m2[2])?$m2[2]:"");
        preg_match_all("/(\n\s*(public)?\s*function\s([_a-zA-Z0-9]+))/", $content, $m3);
        $methods = (isset($m3[3])?$m3[3]:[]);
        return ["namespace"=>$namespace, "class"=>$className, "methods"=>array_combine($methods, $methods), "file"=>$fileName];
    }

    /**
     * Matches sources to tests and executes latter
     *
     * @param array $sourceFiles List of source classes/files found along with adjacent info.
     * @param array $testFiles List of test classes/files found along with adjacent info.
     * @param string $sourceClassName Class in sources folder that needs to be tested.
     * @return array List of unit test results by class and method name.
     */
    private function execute(string $sourceFiles, string $testFiles, string $sourceClassName): array
    {
        $output = [];
        foreach ($sourceFiles as $infoSrc) {
            $srcClassName = $infoSrc["class"];
            if ($sourceClassName && $srcClassName!=$sourceClassName) {
                continue;
            }
            $testClassName = $srcClassName."Test";
            $srcClassNameWithNamespace = ($infoSrc["namespace"]?$infoSrc["namespace"]."\\":"").$srcClassName;
            // check if class is covered
            if (!isset($testFiles[$testClassName])) {
                $output[$srcClassNameWithNamespace][][] = new Result(false, "Class not covered by unit test");
            } elseif ($infoSrc["namespace"]!=$testFiles[$testClassName]["namespace"]) {
                $output[$srcClassNameWithNamespace][][] = new Result(false, "Unit test namespace doesn't match namespace of class");
            } else {
                // load classes
                require_once($infoSrc["file"]);
                require_once($testFiles[$testClassName]["file"]);
                // run tests
                $testClassNameWithNamespace = "\\".($infoSrc["namespace"]?$infoSrc["namespace"]."\\":"").$testClassName;
                $testObject = new $testClassNameWithNamespace();
                foreach ($infoSrc["methods"] as $method) {
                    $testMethodName = $method."Test";
                    if (!isset($testFiles[$testClassName]["methods"][$testMethodName])) {
                        $output[$srcClassNameWithNamespace][$method][] = new Result(false, "Method not covered by unit test");
                    } else {
                        $result = $testObject->{$testMethodName}();
                        if (is_array($result) && !empty($result)) {
                            foreach ($result as $r) {
                                if ($r instanceof Result) {
                                    $output[$testClassNameWithNamespace][$testMethodName][] = $r;
                                } else {
                                    $output[$testClassNameWithNamespace][$testMethodName][] = new Result(false, "Invalid unit test response");
                                }
                            }
                        } elseif ($result instanceof Result) {
                            $output[$testClassNameWithNamespace][$testMethodName][] = $result;
                        } else {
                            $output[$testClassNameWithNamespace][$testMethodName][] = new Result(false, "Invalid unit test response");
                        }
                    }
                }
            }
        }
        return $output;
    }

    /**
     * Gets results of unit test execution
     *
     * @return string[string[Result]] List of unit test results by class and method name.
     */
    public function getResults(): array
    {
        return $this->results;
    }
}
