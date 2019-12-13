<?php
namespace Lucinda\UnitTest\Display;

use \Lucinda\UnitTest\Result;

/**
 * Displays unit test results on console.
 */
class Console implements Output
{
    public function showLine($className, $additionalInfo, Result $unitTestResult)
    {
        echo $className."\t".$additionalInfo."\t".($unitTestResult->hasPassed()?"\e[1;37;44m passed \e[0m":"\e[1;37;41m failed \e[0m")."\t".$unitTestResult->getMessage()."\n";
    }
}
