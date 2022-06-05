<?php

namespace Lucinda\UnitTest\Validator;

use Lucinda\UnitTest\Result;

/**
 * Series of tests useful to validate object values
 */
class Objects
{
    private object $value;

    /**
     * Constructs an object
     *
     * @param object $value
     */
    public function __construct(object $value)
    {
        $this->value = $value;
    }

    /**
     * Checks if object is of expected instance
     *
     * @param  string $expected
     * @param  string $message
     * @return Result
     */
    public function assertInstanceOf(string $expected, string $message=""): Result
    {
        return new Result($this->value instanceof $expected, $message);
    }

    /**
     * Checks if object is not of expected instance
     *
     * @param  string $expected
     * @param  string $message
     * @return Result
     */
    public function assertNotInstanceOf(string $expected, string $message=""): Result
    {
        return new Result(!($this->value instanceof $expected), $message);
    }
}
