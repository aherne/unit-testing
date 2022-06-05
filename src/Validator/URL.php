<?php

namespace Lucinda\UnitTest\Validator;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator\URL\Request;
use Lucinda\UnitTest\Validator\URL\Response;
use Lucinda\UnitTest\Validator\URL\ResultValidator;
use Lucinda\UnitTest\Validator\URL\DataSource;

/**
 * Defines a UNIT TEST pattern for URL testing.
 */
class URL
{
    private Response $response;

    /**
     * Generates a request to an URL based on criteria.
     *
     * @param  DataSource $dataSource
     * @throws \Lucinda\UnitTest\Exception
     */
    public function __construct(DataSource $dataSource)
    {
        $request = new Request($dataSource);
        $this->response = $request->getResponse();
    }

    /**
     * Validates Response based on user defined algorithm.
     *
     * @param  ResultValidator $validator
     * @return \Lucinda\UnitTest\Result
     */
    public function assert(ResultValidator $validator): Result
    {
        return $validator->validate($this->response);
    }
}
