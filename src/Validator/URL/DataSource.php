<?php
namespace Lucinda\UnitTest\Validator\URL;

use Lucinda\UnitTest\Exception;

class DataSource
{
    private $url;
    private $requestMethod = "GET";
    private $requestParameters = [];
    private $requestHeaders = [];
    
    public function __construct(string $url)
    {
        $this->url = $url;
    }
    
    public function getURL(): string
    {
        return $this->url;
    }
    
    public function setRequestMethod(string $requestMethod): void
    {
        if (!in_array($requestMethod, ["GET","POST","PUT","HEAD","DELETE","PATCH","OPTIONS"])) {
            throw new Exception("Invalid request method: ".$requestMethod);
        }
        $this->requestMethod = $requestMethod;
    }
    
    public function getRequestMethod(): string
    {
        return $this->requestMethod;
    }
    
    public function addRequestParameter($name, $value): void
    {
        $this->requestParameters[$name] = $value;
    }
    
    public function getRequestParameters(): array
    {
        return $this->requestParameters;
    }
    
    public function addRequestHeader(string $name, string $value): void
    {
        $this->requestHeaders[$name] = $value;
    }
    
    public function getRequestHeaders(): array
    {
        return $this->requestHeaders;
    }
}

