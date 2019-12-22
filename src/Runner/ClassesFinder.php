<?php
namespace Lucinda\UnitTest\Runner;

use Lucinda\UnitTest\Exception;

/**
 * Finds classes in folder and builds a ClassInfo list
 * TODO: support also abstract classes and interfaces in the future
 */
class ClassesFinder
{
    private $results = [];
    
    /**
     * Finds classes in folder
     *
     * @param string $directory
     * @throws Exception If directory not found
     */
    public function __construct(string $directory)
    {
        $this->setResults($directory);
    }
    
    /**
     * Finds classes in folder and appends them as ClassInfo objects to results
     *
     * @param string $directory Folder to look for files into.
     * @throws Exception If directory not found
     */
    private function setResults(string $directory): array
    {
        if (!is_dir($directory)) {
            throw new Exception("Folder not found: ".$directory);
        }
        $files = scandir($directory);
        foreach ($files as $file) {
            if (in_array($file, [".", ".."])) {
                continue;
            }
            $path = $directory."/".$file;
            if (is_dir($path)) {
                $temp = $this->getFiles($path);
                foreach ($temp as $fileInfo) {
                    $this->results[$fileInfo["class"]] = $fileInfo;
                }
            } elseif (strpos($file, ".php")) {
                $info = $this->readFile($path);
                if ($info["class"]) {
                    $this->results[$info["class"]] = $info;
                }
            }
        }
    }
    
    /**
     * Reads class file and detects namespace (if any), class name, public methods and file location.
     *
     * @param string $fileName Absolute location of class file.
     * @return ClassInfo Class info (namespace, class name, methods, file path)
     */
    private function readFile(string $fileName): ClassInfo
    {
        $classInfo = new ClassInfo();
        $classInfo->filePath = $fileName;
        $content = file_get_contents($fileName);
        $m1 = array();
        preg_match("/(\n\s*namespace\s*([^;]+))/", $content, $m1);
        $classInfo->namespace = (isset($m1[2])?$m1[2]:"");
        $m2 = array();
        preg_match("/(\n\s*class\s*([a-zA-Z0-9]+))/", $content, $m2);
        $classInfo->className = (isset($m2[2])?$m2[2]:"");
        $m3 = array();
        preg_match_all("/(\n\s*public\s+function\s+([_a-zA-Z0-9]+))/", $content, $m3);
        $classInfo->methods = (isset($m3[2])?$m3[2]:[]);
        // strip constructor and destructor
        foreach ($classInfo->methods as $i=>$method) {
            if (in_array($method, ["__construct", "__destruct"])) {
                unset($classInfo->methods[$i]);
            }
        }
    }
    
    /**
     * Gets classes found
     *
     * @return ClassInfo[string]
     */
    public function getResults(): array
    {
        return $this->results;
    }
}
