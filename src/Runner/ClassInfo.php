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
    
    /**
     * @var boolean
     */
    public $isFinal = false;
    
    /**
     * @var boolean
     */
    public $isAbstract = false;
    
    /**
     * @var boolean
     */
    public $isInterface = false;
    
    /**
     * @var string
     */
    public $extends;
    
    /**
     * @var string[]
     */
    public $implements = [];
}
