<?php
namespace Lucinda\UnitTest;

use Lucinda\UnitTest\Runner\UnitTest;

/**
 * Runs unit tests on unix console and displays results
 */
class UnixConsoleRunner extends Runner
{
    /**
     * Displays results of unit tests
     *
     * @param UnitTest[] $results
     */
    protected function display(array $results)
    {
        foreach ($results as $unitTest) {
            echo $unitTest->className."\t".$unitTest->methodName."\t".($unitTest->result->hasPassed()?"\e[1;37;44m passed \e[0m":"\e[1;37;41m failed \e[0m")."\t".$unitTest->result->getMessage()."\n";
        }
    }
}
