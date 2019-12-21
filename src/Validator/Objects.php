<?php
namespace Lucinda\UnitTest\Validator;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Exception;

/**
 * Series of tests useful to validate object values
 */
class Objects
{
    private $value;
    
    /**
     * Constructs an object
     *
     * @param object $value
     * @throws Exception
     */
    public function __construct($value)
    {
        if (!is_object($value)) {
            throw new Exception("Value is not an object!");
        }
        $this->value = $value;
    }
    
    /**
     * Checks if object is of expected instance
     *
     * @param string $expected
     * @param string $message
     * @return Result
     */
    public function assertInstanceOf(string $expected, string $message=""): Result
    {
        return new Result($this->value instanceof $expected, $message);
    }
    
    /**
     * Checks if object is not of expected instance
     *
     * @param string $expected
     * @param string $message
     * @return Result
     */
    public function assertNotInstanceOf(string $expected, string $message=""): Result
    {
        return new Result(!($this->value instanceof $expected), $message);
    }
}
