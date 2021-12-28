<?php
namespace Lucinda\UnitTest\Validator;

use Lucinda\UnitTest\Result;

/**
 * Defines series of assertions to check variables of type integer
 */
class Integers
{
    private int $value;
    
    /**
     * Constructs a integer
     *
     * @param int $value
     */
    public function __construct(int $value)
    {
        $this->value = $value;
    }
    
    /**
     * Asserts if integer equals one expected
     *
     * @param int $expected
     * @param string $message
     * @return Result
     */
    public function assertEquals(int $expected, string $message=""): Result
    {
        return new Result($expected==$this->value, $message);
    }
    
    /**
     * Asserts if integer is different from one expected
     *
     * @param int $expected
     * @param string $message
     * @return Result
     */
    public function assertNotEquals(int $expected, string $message=""): Result
    {
        return new Result($expected!=$this->value, $message);
    }
    
    /**
     * Asserts if integer is greater than one expected
     *
     * @param integer $expected
     * @param string $message
     * @return Result
     */
    public function assertGreater(int $expected, string $message=""): Result
    {
        return new Result($expected>$this->value, $message);
    }
    
    /**
     * Asserts if integer is greater or equals one expected
     *
     * @param integer $expected
     * @param string $message
     * @return Result
     */
    public function assertGreaterEquals(int $expected, string $message=""): Result
    {
        return new Result($expected>=$this->value, $message);
    }
    
    /**
     * Asserts if integer is smaller than one expected
     *
     * @param integer $expected
     * @param string $message
     * @return Result
     */
    public function assertSmaller(int $expected, string $message=""): Result
    {
        return new Result($expected<$this->value, $message);
    }
    
    /**
     * Asserts if integer is smaller or equals one expected
     *
     * @param integer $expected
     * @param string $message
     * @return Result
     */
    public function assertSmallerEquals(int $expected, string $message=""): Result
    {
        return new Result($expected<=$this->value, $message);
    }
}
