<?php
namespace Lucinda\UnitTest\Validator;

use Lucinda\UnitTest\Result;

/**
 * Blueprint for validating unit/functional tests
 */
abstract class AbstractValidator
{
    protected $unitTestResult;

    /**
     * Gets unit test result
     *
     * @return Result
     */
    public function getResult(): Result
    {
        return $this->unitTestResult;
    }
}
