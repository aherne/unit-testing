<?php

namespace Lucinda\UnitTest\Runner;

use Lucinda\UnitTest\Result;

/**
 * Struct holding information about an unit test result
 */
class UnitTest
{
    public string $className="";
    public string $methodName="";
    public ?Result $result = null;
}
