<?php
namespace Lucinda\UnitTest\Validator;

use Lucinda\UnitTest\Result;

/**
 * Defines series of assertions to check variables of type integer
 */
class Integers
{
    public static function assertEquals(int $expected, int $actual, string $message=""): Result
    {
        return new Result($expected==$actual, $message);
    }

    public static function assertDifferent(int $expected, int $actual, string $message=""): Result
    {
        return new Result($expected!=$actual, $message);
    }

    public static function assertGreater(int $expected, int $actual, string $message=""): Result
    {
        return new Result($expected>$actual, $message);
    }

    public static function assertGreaterEquals(int $expected, int $actual, string $message=""): Result
    {
        return new Result($expected>=$actual, $message);
    }

    public static function assertSmaller(int $expected, int $actual, string $message=""): Result
    {
        return new Result($expected<$actual, $message);
    }

    public static function assertSmallerEquals(int $expected, int $actual, string $message=""): Result
    {
        return new Result($expected<=$actual, $message);
    }
}
