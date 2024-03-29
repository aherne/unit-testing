<?php

namespace Lucinda\UnitTest\Runner;

/**
 * Struct holding information about a class detected
 */
class ClassInfo
{
    public string $namespace;
    public string $className;
    /**
     * @var string[]
     */
    public array $methods=[];
    public string $filePath;
    public bool $isFinal = false;
    public bool $isAbstract = false;
    public bool $isInterface = false;
    public bool $isEnum = false;
    public string $extends = "";
    /**
     * @var string[]
     */
    public array $implements = [];
}
