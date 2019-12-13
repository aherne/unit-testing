<?php
namespace Lucinda\UnitTest\Display;

use \Lucinda\UnitTest\Result;

/**
 * Blueprint for displaying unit test result
 */
interface Output
{
    /**
     * Displays a line
     *
     * @param string $className Name of unit test class / class under testing
     * @param string $additionalInfo Additional information that makes test specific per class (eg: method name)
     * @param Result $unitTestResult Encapsulated result of unit test.
     */
    public function showLine($className, $additionalInfo, Result $unitTestResult);
}
