<?php
namespace Lucinda\UnitTest\Validator;

use Lucinda\UnitTest\Result;

/**
 * Defines series of assertions to check variables of type string
 */
class Strings
{
    private string $value;
    
    /**
     * Constructs a string
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }
    
    /**
     * Asserts if string is empty
     *
     * @param string $message
     * @return Result
     */
    public function assertEmpty(string $message=""): Result
    {
        return new Result(($this->value?false:true), $message);
    }

    /**
     * Asserts if string is not empty
     *
     * @param string $message
     * @return Result
     */
    public function assertNotEmpty(string $message=""): Result
    {
        return new Result(($this->value?true:false), $message);
    }

    /**
     * Asserts if string equals expected
     *
     * @param string $expected
     * @param string $message
     * @return Result
     */
    public function assertEquals(string $expected, string $message=""): Result
    {
        return new Result($expected==$this->value, $message);
    }

    /**
     * Asserts if string is different from expected
     *
     * @param string $expected
     * @param string $message
     * @return Result
     */
    public function assertNotEquals(string $expected, string $message=""): Result
    {
        return new Result($expected!=$this->value, $message);
    }
    
    /**
     * Asserts if string contains expected
     *
     * @param string $expected
     * @param string $message
     * @return Result
     */
    public function assertContains(string $expected, string $message=""): Result
    {
        return new Result(strpos($this->value, $expected)!==false, $message);
    }
    
    /**
     * Asserts if string doesn't contain expected
     *
     * @param string $expected
     * @param string $message
     * @return Result
     */
    public function assertNotContains(string $expected, string $message=""): Result
    {
        return new Result(strpos($this->value, $expected)===false, $message);
    }
    
    /**
     * Asserts if string is of expected size
     *
     * @param int $count
     * @param string $message
     * @return Result
     */
    public function assertSize(int $count, string $message=""): Result
    {
        return new Result(strlen($this->value)==$count, $message);
    }
    
    /**
     * Asserts if string is not of expected size
     *
     * @param int $count
     * @param string $message
     * @return Result
     */
    public function assertNotSize(int $count, string $message=""): Result
    {
        return new Result(strlen($this->value)!=$count, $message);
    }
}
