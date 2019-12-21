<?php
namespace Lucinda\UnitTest\Validator;

use Lucinda\UnitTest\Validator\URL\Request;
use Lucinda\UnitTest\Validator\URL\ResultValidator;

/**
 * Defines a UNIT TEST pattern for URL testing.
 */
class URL
{
    private $response;

    /**
     * Generates a request to an URL based on criteria.
     *
     * @param string $url URL to use in request.
     * @param string $requestMethod HTTP request method to use when calling URL. Default: GET
     * @param array $requestParameters List of parameters to send in request specific to request method. Default: none
     * @param array $requestHeaders List of headers to send along with URL request.
     */
    public function __construct(string $url, string $requestMethod="GET", array $requestParameters=[], array $requestHeaders=[])
    {
        $request = new Request($url, $requestMethod, $requestParameters, $requestHeaders);
        $this->response = $request->getResponse();
    }
    
    /**
     * Validates Response based on user defined algorithm.
     *
     * @param ResultValidator $validator
     * @return \Lucinda\UnitTest\Result
     */
    public function assert(ResultValidator $validator)
    {
        return $validator->validate($this->response);
    }
}
