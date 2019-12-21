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
     * @param \PDOStatement $statementResults
     * @return Result
     */
    public function validate(\PDOStatement $statementResults): Result;
}
