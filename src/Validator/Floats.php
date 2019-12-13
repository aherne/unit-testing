<?php
namespace Lucinda\UnitTest\Validator;

use Lucinda\UnitTest\Result;

/**
 * Defines series of assertions to check variables of type float/double
 */
class Floats
{
    public static function assertEquals(float $expected, float $actual, string $message=""): Result
    {
        return new Result($expected==$actual, $message);
    }

    public static function assertDifferent(float $expected, float $actual, string $message=""): Result
    {
        return new Result($expected!=$actual, $message);
    }

    public static function assertGreater(float $expected, float $actual, string $message=""): Result
    {
        return new Result($expected>$actual, $message);
    }

    public static function assertGreaterEquals(float $expected, float $actual, string $message=""): Result
    {
        return new Result($expected>=$actual, $message);
    }

    public static function assertSmaller(float $expected, float $actual, string $message=""): Result
    {
        return new Result($expected<$actual, $message);
    }

    public static function assertSmallerEquals(float $expected, float $actual, string $message=""): Result
    {
        return new Result($expected<=$actual, $message);
    }
}
