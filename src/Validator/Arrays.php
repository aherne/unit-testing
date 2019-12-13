<?php
namespace Lucinda\UnitTest\Validator;

use Lucinda\UnitTest\Result;

/**
 * Defines series of assertions to check variables of type array
 */
class Arrays
{
    public static function assertEmpty(array $actual, string $message=""): Result
    {
        return new Result((empty($actual)?true:false), $message);
    }

    public static function assertNotEmpty(array $actual, string $message=""): Result
    {
        return new Result((!empty($actual)?true:false), $message);
    }

    public static function assertEquals(array $expected, array $actual, string $message=""): Result
    {
        return new Result($expected==$actual, $message);
    }

    public static function assertNotEquals(array $expected, array $actual, string $message=""): Result
    {
        return new Result($expected!=$actual, $message);
    }

    public static function assertContainsKey(array $array, $key, string $message=""): Result
    {
        return new Result(isset($array[$key]), $message);
    }

    public static function assertNotContainsKey(array $array, $key, string $message=""): Result
    {
        return new Result(!isset($array[$key]), $message);
    }

    public static function assertContainsValue(array $array, $value, string $message=""): Result
    {
        return new Result(in_array($value, $array), $message);
    }

    public static function assertNotContainsValue(array $array, $value, string $message=""): Result
    {
        return new Result(!in_array($value, $array), $message);
    }

    public static function assertSize(array $array, int $count, string $message=""): Result
    {
        return new Result(sizeof($array)==$count, $message);
    }
}
