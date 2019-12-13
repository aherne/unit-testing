<?php
namespace Lucinda\UnitTest\Validator\SQL;

use \Lucinda\UnitTest\Result;

/**
 * Blueprint for validating SQL query results
 */
interface ResultValidator
{
    /**
     * Validates results of an SQL query execution into a Result object
     *
     * @param \Lucinda\SQL\StatementResults $statementResults
     * @return Result
     */
    public function validate(\Lucinda\SQL\StatementResults $statementResults): Result;
}
