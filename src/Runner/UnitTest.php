<?php
namespace Lucinda\UnitTest\Runner;

use Lucinda\UnitTest\Result;

/**
 * Struct holding information about an unit test result
 */
class UnitTest
{
    /**
     * @var string
     */
    public $className="";
    /**
     * @var string
     */
    public $methodName="";
    /**
     * @var Result
     */
    public $result;
}
