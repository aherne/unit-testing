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
     * @param Response $response
     * @return Result
     */
    public function validate(Response $response): Result;
}
