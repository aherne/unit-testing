<?php
namespace Lucinda\UnitTest;

use Lucinda\UnitTest\Runner\UnitTest;

/**
 * Runs unit tests programmatically and displays a JSON in response
 */
class JsonRunner extends Runner
{
    /**
     * Displays results of unit tests
     *
     * @param UnitTest[] $results
     */
    protected function display(array $results): void
    {
        echo json_encode($results);
    }
}

