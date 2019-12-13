<?php
namespace Lucinda\UnitTest\Validator;

use \Lucinda\UnitTest\Result;
use \Lucinda\UnitTest\Validator\SQL\ResultValidator;

/**
 * Defines a UNIT TEST pattern for SQL testing.
 */
class SQL extends AbstractValidator
{
    /**
     * Executes query and applies user defined validator to results.
     *
     * @param string $query SQL query to prepare.
     * @param string[string] $boundParameters List of parameters to bind
     * @param ResultValidator $validator Algorithm to validate SQL execution results.
     */
    public function __construct(string $query, array $boundParameters=[], ResultValidator $validator)
    {
        try {
            $resultSet = $this->execute($query, $boundParameters);
            $this->unitTestResult = $validator->validate($resultSet);
        } catch (\Exception $e) {
            $this->unitTestResult = new Result(false, $e->getMessage());
        }
    }

    /**
     * Executes query and applies user defined validator to results.
     *
     * @param string $query
     * @param string[string] $boundParameters
     * @return \Lucinda\SQL\StatementResults
     */
    private function execute(string $query, array $boundParameters=[]): \Lucinda\SQL\StatementResults
    {
        $connection = \Lucinda\SQL\ConnectionSingleton::getInstance();
        $preparedStatement = $connection->createPreparedStatement();
        $preparedStatement->prepare($query);
        return $preparedStatement->execute($boundParameters);
    }
}
