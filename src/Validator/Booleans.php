<?php
namespace Lucinda\UnitTest\Validator;

use Lucinda\UnitTest\Result;

/**
 * Defines series of assertions to check variables of type boolean
 */
class Booleans
{
    public static function assertTrue(bool $actual, string $message=""): Result
    {
        return new Result($actual?true:false, $message);
    }

    public static function assertFalse(bool $actual, string $message=""): Result
    {
        return new Result($actual?false:true, $message);
    }
}
