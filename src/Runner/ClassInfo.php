<?php
namespace Lucinda\UnitTest\Runner;

/**
 * Struct holding information about a class detected
 */
class ClassInfo
{
    /**
     * @var string
     */
    public $namespace;
    /**
     * @var string
     */
    public $className;
    /**
     * @var string[]
     */
    public $methods=[];
    /**
     * @var string
     */
    public $filePath;
}
