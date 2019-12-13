<?php
namespace Lucinda\UnitTest\Validator;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator\URL\Request;
use Lucinda\UnitTest\Validator\URL\RequestException;
use Lucinda\UnitTest\Validator\URL\ResponseException;
use Lucinda\UnitTest\Validator\URL\ResultValidator;

/**
 * Defines a UNIT TEST pattern for URL testing.
 */
class URL extends AbstractValidator
{

    /**
     * Generates a request to an URL based on criteria.
     *
     * @param string $url URL to use in request.
     * @param string $requestMethod HTTP request method to use when calling URL. Default: GET
     * @param array $requestParameters List of parameters to send in request specific to request method. Default: none
     * @param array $requestHeaders List of headers to send along with URL request.
     * @param ResultValidator $validator Algorithm to validate URL execution results.
     */
    public function __construct(string $url, string $requestMethod="GET", array $requestParameters=[], array $requestHeaders=[], ResultValidator $validator)
    {
        try {
            $rv = new Request($url, $requestMethod, $requestParameters, $requestHeaders);
            if ($rv->getResponse()->getStatus()!=200) {
                throw new ResponseException($rv->getResponse()->getStatus());
            }
            $this->unitTestResult = $validator->validate($rv->getResponse());
        } catch (RequestException $e) {
            $this->unitTestResult = new Result(false, "request failed with http status 408");
        } catch (ResponseException $e) {
            $this->unitTestResult = new Result(false, "request failed with http status ".$e->getMessage());
        }
    }
}
