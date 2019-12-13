<?php
namespace Lucinda\UnitTest\Validator\URL;

use Lucinda\UnitTest\Result;

/**
 * Blueprint for validating results of URL execution
 */
interface ResultValidator
{
    /**
     * Validates results of URL execution into a Result object
     *
     * @param \Lucinda\SQL\StatementResults $statementResults
     * @return Result
     */
    public function validate(Response $response): Result;
}
