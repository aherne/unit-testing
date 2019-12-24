<?php
namespace Lucinda\UnitTest;

use Lucinda\UnitTest\Runner\UnitTest;

/**
 * Runs unit tests on unix console and displays results
 */
class ConsoleRunner extends Controller
{
    /**
     * Displays results of unit tests
     *
     * @param UnitTest[] $results
     */
    protected function handle(array $results): void
    {
        // get max lengths
        $maxClassLength = 5;
        $maxMethodLength = 6;
        $maxDescriptionLength = 10;
        foreach ($results as $unitTest) {
            if (strlen($unitTest->className)>$maxClassLength) {
                $maxClassLength = strlen($unitTest->className);
            }
            if (strlen($unitTest->methodName)>$maxMethodLength) {
                $maxMethodLength = strlen($unitTest->methodName);
            }
            if (strlen($unitTest->methodName)>$maxMethodLength) {
                $maxMethodLength = strlen($unitTest->methodName);
            }
            if (strlen($unitTest->result->getMessage())>$maxDescriptionLength) {
                $maxDescriptionLength = strlen($unitTest->result->getMessage());
            }
        }
        
        // compile lines
        $emptyLineLength = $maxClassLength+4+$maxMethodLength+4+6+4+$maxDescriptionLength+4;
        echo str_repeat("-", $emptyLineLength)."\n";
        echo "| CLASS".str_repeat(" ", $maxClassLength-5)." | METHOD".str_repeat(" ", $maxMethodLength-6)." | STATUS | DESCRIPTION".str_repeat(" ", $maxDescriptionLength-11)." |\n";
        foreach ($results as $unitTest) {
            echo str_repeat("-", $emptyLineLength)."\n";
            echo "| ".$unitTest->className.str_repeat(" ", $maxClassLength-strlen($unitTest->className))." | ".$unitTest->methodName.str_repeat(" ", $maxMethodLength-strlen($unitTest->methodName))." | ".($unitTest->result->hasPassed()?"passed":"failed")." | ".$unitTest->result->getMessage().str_repeat(" ", $maxDescriptionLength-strlen($unitTest->result->getMessage()))." |\n";
        }
        echo str_repeat("-", $emptyLineLength)."\n";
    }
}
