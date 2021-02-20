<?php
namespace Lucinda\UnitTest\Validator\URL;

use Lucinda\UnitTest\Exception;

/**
 * Encapsulates information to use in connecting to an URL
 */
class DataSource
{
    private $url;
    private $requestMethod = "GET";
    private $requestParameters = [];
    private $requestHeaders = [];
    
    /**
     * Sets url to connect to
     *
     * @param string $url
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }
    
    /**
     * Gets url to connect to
     *
     * @return string
     */
    public function getURL(): string
    {
        return $this->url;
    }
    
    /**
     * Sets HTTP request method to use in connection
     *
     * @param string $requestMethod
     * @throws Exception
     */
    public function setRequestMethod(string $requestMethod): void
    {
        if (!in_array($requestMethod, ["GET","POST","PUT","HEAD","DELETE","PATCH","OPTIONS"])) {
            throw new Exception("Invalid request method: ".$requestMethod);
        }
        $this->requestMethod = $requestMethod;
    }
    
    /**
     * Gets HTTP request method to use in connection
     *
     * @return string
     */
    public function getRequestMethod(): string
    {
        return $this->requestMethod;
    }
    
    /**
     * Adds a request parameter by name and value
     *
     * @param mixed $name
     * @param mixed $value
     */
    public function addRequestParameter($name, $value): void
    {
        $this->requestParameters[$name] = $value;
    }
    
    /**
     * Gets all request parameters added
     *
     * @return array
     */
    public function getRequestParameters(): array
    {
        return $this->requestParameters;
    }
    
    /**
     * Adds a request header by name and value
     *
     * @param string $name
     * @param string $value
     */
    public function addRequestHeader(string $name, string $value): void
    {
        $this->requestHeaders[] = $name.": ".$value;
    }
    
    /**
     * Gets all request headers added
     *
     * @return array
     */
    public function getRequestHeaders(): array
    {
        return $this->requestHeaders;
    }
}
