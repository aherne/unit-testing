<?php
namespace Lucinda\UnitTest\Runner;

use Lucinda\UnitTest\Exception;

/**
 * Finds classes in folder and builds a ClassInfo list
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
        foreach ($this->results as $classInfo) {
            if (!$classInfo->isAbstract && !$classInfo->isInterface)
            $classInfo->methods = $this->getInheritedMethods($classInfo);
        }
    }
    
    /**
     * Finds classes in folder and appends them as ClassInfo objects to results
     *
     * @param string $directory Folder to look for files into.
     * @throws Exception If directory not found
     */
    private function setResults(string $directory)
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
                if ($info->className) {
                    $this->results[($info->namespace?$info->namespace."\\":"").$info->className] = $info;
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
        preg_match("/(\n[\s|\t]*namespace\s*([^;]+))/", $content, $m1);
        $classInfo->namespace = (isset($m1[2])?$m1[2]:"");
        
        $m5 = array();
        preg_match_all("/(\n[\s|\t]*use\s+([a-zA-Z0-9_\\\]+)\s+as\s+([a-zA-Z0-9_]+)\s*;)/", $content, $m5);
        $correspondences = [];
        foreach($m5[1] as $i=>$value) {
            $correspondences[$m5[2][$i]] = $m5[1][$i];
        }
        
        $m2 = array();
        preg_match("/(\n[\s|\t]*(final)?\s*(abstract)?\s*(class|interface)\s*([a-zA-Z0-9_]+))\s*(extends\s+([a-zA-Z0-9_\\\]+))?\s*(implements\s+([a-zA-Z0-9_\\\,\s]+))?/", $content, $m2);
        $classInfo->isFinal = ($m2[2]?true:false);
        $classInfo->isAbstract = ($m2[3]?true:false);
        $classInfo->isInterface = $m2[4]=="interface";
        $classInfo->className = $m2[5];
        $classInfo->extends = (!empty($m2[7])?$this->getFullClassName($m2[7], $correspondences,$classInfo->namespace):"");
        if (!empty($m2[9])) {
            $matches = array();
            preg_match_all("/([_a-zA-Z0-9\\\]+))/", $m2[9], $matches);
            foreach ($matches[1] as $className) {
                $classInfo->implements[] = $this->getFullClassName($className, $correspondences,$classInfo->namespace);
            }            
        }
        
        $m3 = array();
        preg_match_all("/(\n[\s|\t]*(final)?\s*(public)?\s*function\s+([_a-zA-Z0-9]+))/", $content, $m3);
        $methods = (isset($m3[4])?$m3[4]:[]);
        foreach ($methods as $method) {
            if (!in_array($method, ["__construct", "__destruct"])) {
                $classInfo->methods[$method] = $method;
            }
        }
        return $classInfo;
    }
    
    private function getFullClassName($name, $correspondences, $namespace)
    {
        return (isset($correspondences[$name])?$correspondences[$name]:($name[0]=="\\"?$name:($namespace?$namespace."\\".$name:$name)));
    }
    
    /**
     * Gets inherited methods for class
     * 
     * @param ClassInfo $classInfo
     * @return string[]
     */
    private function getInheritedMethods(ClassInfo $classInfo)
    {
        $methods = $classInfo->methods;
        if ($classInfo->extends) {
            $newMethods = $this->getInheritedMethods($this->results[$classInfo->extends]);
            foreach ($newMethods as $methodName) {
                $methods[$methodName] = $methodName;
            }
        }
        if ($classInfo->implements) {
            foreach ($classInfo->implements as $className) {
                $newMethods = $this->getInheritedMethods($this->results[$className]);
                foreach ($newMethods as $methodName) {
                    $methods[$methodName] = $methodName;
                }
            }
        }
        return $methods;
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
