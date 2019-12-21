<?php
namespace Lucinda\UnitTest;

use Lucinda\UnitTest\Runner\UnitTest;

/**
 * Runs unit tests on windows command prompt and displays results
 */
class WindowsConsoleRunner extends Runner
{
    /**
     * Displays results of unit tests
     *
     * @param UnitTest[] $results
     */
    protected function display(array $results): void
    {
        foreach ($results as $unitTest) {
            echo $unitTest->className."\t".$unitTest->methodName."\t".($unitTest->result->hasPassed()?"passed":"failed")."\t".$unitTest->result->getMessage()."\r\n";
        }
    }
}
