<?php
namespace Lucinda\UnitTest\Validator\URL;

/**
 * Encapsulates response after URL was called via cURL
 */
class Response
{
    private $statusCode;
    private $body;
    private $headers=[];

    /**
     * Calls internal setters about cURL results
     *
     * @param integer $httpStatusCode HTTP status code of response
     * @param string $body Body of response
     * @param string[string] $headers HTTP headers that came along with response
     */
    public function __construct(int $httpStatusCode, string $body, array $headers)
    {
        $this->setStatus($httpStatusCode);
        $this->setBody($body);
        $this->setHeaders($headers);
    }

    /**
     * Sets HTTP status that came with response
     *
     * @param integer $httpStatusCode
     */
    private function setStatus(int $httpStatusCode): void
    {
        $this->statusCode = $httpStatusCode;
    }

    /**
     * Sets response body.
     *
     * @param string $result
     */
    private function setBody(string $result): void
    {
        $this->body = $result;
    }

    /**
     * Sets HTTP headers that came with response.
     *
     * @param string[string] $headers
     */
    private function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    /**
     * Gets HTTP status that came with response.
     *
     * @return integer
     */
    public function getStatus(): int
    {
        return $this->statusCode;
    }

    /**
     * Gets response body
     *
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Gets HTTP headers that came along with response.
     *
     * @return string[string]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
