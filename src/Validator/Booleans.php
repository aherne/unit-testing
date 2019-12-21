<?php
namespace Lucinda\UnitTest\Validator;

use Lucinda\UnitTest\Result;

/**
 * Defines series of assertions to check variables of type boolean
 */
class Booleans
{
    private $value;
    
    /**
     * Constructs a boolean
     *
     * @param bool $value
     */
    public function __construct(bool $value)
    {
        $this->value = $value;
    }
    
    /**
     * Asserts if boolean is true
     *
     * @param string $message
     * @return Result
     */
    public function assertTrue(string $message=""): Result
    {
        return new Result($this->value?true:false, $message);
    }
    
    /**
     * Asserts if boolean is false
     *
     * @param string $message
     * @return Result
     */
    public function assertFalse(string $message=""): Result
    {
        return new Result($this->value?false:true, $message);
    }
}
