<?php
namespace Lucinda\UnitTest\Validator;

use Lucinda\UnitTest\Result;

/**
 * Series of tests useful to validate object values
 */
class Objects
{
    public static function assertInstanceOf($expected, $actual, string $message=""): Result
    {
        return new Result($actual instanceof $expected, $message);
    }

    public static function assertNotInstanceOf($expected, $actual, string $message=""): Result
    {
        return new Result(!($actual instanceof $expected), $message);
    }
}
