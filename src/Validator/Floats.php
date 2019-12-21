<?php
namespace Lucinda\UnitTest\Validator;

use Lucinda\UnitTest\Result;

/**
 * Defines series of assertions to check variables of type float/double
 */
class Floats
{
    private $value;
    
    /**
     * Constructs a float
     *
     * @param float $value
     */
    public function __construct(float $value)
    {
        $this->value = $value;
    }
    
    /**
     * Asserts if float equals one expected
     *
     * @param float $expected
     * @param string $message
     * @return Result
     */
    public function assertEquals(float $expected, string $message=""): Result
    {
        return new Result($expected==$this->value, $message);
    }
    
    /**
     * Asserts if float is different from one expected
     *
     * @param float $expected
     * @param string $message
     * @return Result
     */
    public function assertDifferent(float $expected, string $message=""): Result
    {
        return new Result($expected!=$this->value, $message);
    }
    
    /**
     * Asserts if float is greater than one expected
     *
     * @param float $expected
     * @param string $message
     * @return Result
     */
    public function assertGreater(float $expected, string $message=""): Result
    {
        return new Result($expected>$this->value, $message);
    }
    
    /**
     * Asserts if float is greater or equals one expected
     *
     * @param float $expected
     * @param string $message
     * @return Result
     */
    public function assertGreaterEquals(float $expected, string $message=""): Result
    {
        return new Result($expected>=$this->value, $message);
    }
    
    /**
     * Asserts if float is smaller than one expected
     *
     * @param float $expected
     * @param string $message
     * @return Result
     */
    public function assertSmaller(float $expected, string $message=""): Result
    {
        return new Result($expected<$this->value, $message);
    }
    
    /**
     * Asserts if float is smaller or equals one expected
     *
     * @param float $expected
     * @param string $message
     * @return Result
     */
    public function assertSmallerEquals(float $expected, string $message=""): Result
    {
        return new Result($expected<=$this->value, $message);
    }
}
