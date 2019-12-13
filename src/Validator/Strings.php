<?php
namespace Lucinda\UnitTest\Validator;

use Lucinda\UnitTest\Result;

/**
 * Defines series of assertions to check variables of type string
 */
class Strings
{
    public static function assertEmpty(string $actual, string $message=""): Result
    {
        return new Result(($actual?false:true), $message);
    }

    public static function assertNotEmpty(string $actual, string $message=""): Result
    {
        return new Result(($actual?true:false), $message);
    }

    public static function assertEquals(string $expected, string $actual, string $message=""): Result
    {
        return new Result($expected==$actual, $message);
    }

    public static function assertDifferent(string $expected, string $actual, string $message=""): Result
    {
        return new Result($expected!=$actual, $message);
    }

    public static function assertIdentical(string $expected, string $actual, string $message=""): Result
    {
        return new Result($expected===$actual, $message);
    }

    public static function assertNotIdentical(string $expected, string $actual, string $message=""): Result
    {
        return new Result($expected!==$actual, $message);
    }

    public static function assertEqualsIgnoreCase(string $expected, string $actual, string $message=""): Result
    {
        return new Result(strtolower($expected)==strtolower($actual), $message);
    }

    public static function assertDifferentIgnoreCase(string $expected, string $actual, string $message=""): Result
    {
        return new Result(strtolower($expected)!=strtolower($actual), $message);
    }

    public static function assertSize(string $string, int $count, string $message=""): Result
    {
        return new Result(sizeof($string)==$count, $message);
    }
}
