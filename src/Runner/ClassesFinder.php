<?php

namespace Lucinda\UnitTest\Runner;

use Lucinda\UnitTest\Exception;

/**
 * Finds classes in folder and builds a ClassInfo list
 */
class ClassesFinder
{
    /**
     * @var array<string,ClassInfo>
     */
    private array $results = [];

    /**
     * Finds classes in folder
     *
     * @param  string $directory
     * @throws Exception If directory not found
     */
    public function __construct(string $directory)
    {
        $this->setResults($directory);
        foreach ($this->results as $classInfo) {
            if (!$classInfo->isAbstract && !$classInfo->isInterface && !$classInfo->isEnum) {
                $classInfo->methods = $this->getInheritedMethods($classInfo);
            }
        }
    }

    /**
     * Finds classes in folder and appends them as ClassInfo objects to results
     *
     * @param  string $directory Folder to look for files into.
     * @throws Exception If directory not found
     */
    private function setResults(string $directory): void
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
                $this->setResults($path);
            } elseif (strpos($file, ".php")) {
                $reader = new ClassInfoReader($path);
                $info = $reader->getClassInfo();
                if ($info->className) {
                    $this->results[($info->namespace ? $info->namespace."\\" : "").$info->className] = $info;
                }
            }
        }
    }

    /**
     * Gets inherited methods for class
     *
     * @param  ClassInfo $classInfo
     * @return string[]
     */
    private function getInheritedMethods(ClassInfo $classInfo): array
    {
        // collect classes inherited or interfaces implemented
        $classes = [];
        if ($classInfo->extends) {
            $classes[] = $this->getNormalizedClassName($classInfo->extends);
        }
        if ($classInfo->implements) {
            foreach ($classInfo->implements as $className) {
                $classes[] = $this->getNormalizedClassName($className);
            }
        }

        // gets public methods inherited classes/interfaces have
        $methods = $classInfo->methods;
        foreach ($classes as $className) {
            if ($className && isset($this->results[$className])) {
                $newMethods = $this->getInheritedMethods($this->results[$className]);
                foreach ($newMethods as $methodName) {
                    $methods[$methodName] = $methodName;
                }
            }
        }

        return $methods;
    }

    /**
     * Gets normalized class name,
     *
     * @param  string $className
     * @return string
     */
    private function getNormalizedClassName(string $className): string
    {
        if ($className[0]=="\\") {
            $className = substr($className, 1);
        }

        if (!str_contains($className, "\\")) {
            return ""; // global namespace class
        } else {
            return $className;
        }
    }

    /**
     * Gets classes found
     *
     * @return array<string,ClassInfo>
     */
    public function getResults(): array
    {
        return $this->results;
    }
}
