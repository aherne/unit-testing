<?php
namespace Lucinda\UnitTest;

use Lucinda\UnitTest\Runner\UnitTest;

/**
 * Runs unit tests programmatically and displays a JSON in response
 */
class JsonController extends Controller
{
    /**
     * Displays results of unit tests
     *
     * @param UnitTest[] $results
     */
    protected function handle(array $results): void
    {
        echo json_encode($results);
    }
}
