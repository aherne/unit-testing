<?php

namespace Lucinda\UnitTest\Validator;

use Lucinda\UnitTest\Result;

/**
 * Defines series of assertions to check variables of type array
 */
class Arrays
{
    /**
     * @var array<mixed>
     */
    private array $value;

    /**
     * Constructs an array
     *
     * @param array<mixed> $value
     */
    public function __construct(array $value)
    {
        $this->value = $value;
    }

    /**
     * Asserts if array is empty
     *
     * @param  string $message
     * @return Result
     */
    public function assertEmpty(string $message=""): Result
    {
        return new Result(empty($this->value), $message);
    }

    /**
     * Asserts if array is not empty
     *
     * @param  string $message
     * @return Result
     */
    public function assertNotEmpty(string $message=""): Result
    {
        return new Result(!empty($this->value), $message);
    }

    /**
     * Asserts if array equals one expected
     *
     * @param  array<mixed> $expected
     * @param  string       $message
     * @return Result
     */
    public function assertEquals(array $expected, string $message=""): Result
    {
        return new Result($expected==$this->value, $message);
    }

    /**
     * Asserts if array does not equal one expected
     *
     * @param  array<mixed> $expected
     * @param  string       $message
     * @return Result
     */
    public function assertNotEquals(array $expected, string $message=""): Result
    {
        return new Result($expected!=$this->value, $message);
    }

    /**
     * Asserts if array is identical with one expected
     *
     * @param  array<mixed> $expected
     * @param  string       $message
     * @return Result
     */
    public function assertIdentical(array $expected, string $message=""): Result
    {
        return new Result($expected===$this->value, $message);
    }

    /**
     * Asserts if array is not identical with one expected
     *
     * @param  array<mixed> $expected
     * @param  string       $message
     * @return Result
     */
    public function assertNotIdentical(array $expected, string $message=""): Result
    {
        return new Result($expected!==$this->value, $message);
    }

    /**
     * Asserts if array contains key
     *
     * @param  mixed  $key
     * @param  string $message
     * @return Result
     */
    public function assertContainsKey($key, string $message=""): Result
    {
        return new Result(isset($this->value[$key]), $message);
    }

    /**
     * Asserts if array does not contain key
     *
     * @param  mixed  $key
     * @param  string $message
     * @return Result
     */
    public function assertNotContainsKey($key, string $message=""): Result
    {
        return new Result(!isset($this->value[$key]), $message);
    }

    /**
     * Asserts if array contains value
     *
     * @param  mixed  $value
     * @param  string $message
     * @return Result
     */
    public function assertContainsValue($value, string $message=""): Result
    {
        return new Result(in_array($value, $this->value), $message);
    }

    /**
     * Asserts if array does not contain value
     *
     * @param  mixed  $value
     * @param  string $message
     * @return Result
     */
    public function assertNotContainsValue($value, string $message=""): Result
    {
        return new Result(!in_array($value, $this->value), $message);
    }

    /**
     * Asserts if array is of expected size
     *
     * @param  int    $count
     * @param  string $message
     * @return Result
     */
    public function assertSize(int $count, string $message=""): Result
    {
        return new Result(sizeof($this->value)==$count, $message);
    }

    /**
     * Asserts if array is not of expected size
     *
     * @param  int    $count
     * @param  string $message
     * @return Result
     */
    public function assertNotSize(int $count, string $message=""): Result
    {
        return new Result(sizeof($this->value)!=$count, $message);
    }
}
