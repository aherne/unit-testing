<?php
namespace Lucinda\UnitTest\Validator\URL;

/**
 * Encapsulates a request to an URL via cURL, supporting all HTTP verbs (GET, POST, PUT, DELETE)
 */
class Request
{
    private $response;

    /**
     * Generates a request to an URL based on criteria.
     *
     * @param string $link URL to use in request.
     * @param string $requestMethod HTTP request method to use when calling URL. Default: GET
     * @param array $requestParameters List of parameters to send in request specific to request method. Default: none
     * @param array $requestHeaders List of headers to send along with URL request.
     * @throws RequestException If URL called is non-responsive.
     */
    public function __construct(string $link, string $requestMethod="GET", array $requestParameters=[], array $requestHeaders=[])
    {
        $this->setResponse($link, $requestMethod, $requestHeaders, $requestParameters);
    }

    /**
     * Runs request and encapsulates results into a Response object
     *
     * @param string $link URL to use in request.
     * @param string $requestMethod HTTP request method to use when calling URL. Default: GET
     * @param array $requestParameters List of parameters to send in request specific to request method. Default: none
     * @param array $requestHeaders List of headers to send along with URL request.
     * @throws RequestException If URL called is non-responsive.
     */
    private function setResponse(string $link, string $requestMethod, array $requestParameters, array $requestHeaders): void
    {
        $headers = [];
        $ch = curl_init();
        if ($requestMethod=="GET") {
            curl_setopt($ch, CURLOPT_URL, $link.($requestParameters?"?".http_build_query($requestParameters):""));
        } else {
            curl_setopt($ch, CURLOPT_URL, $link);
            if ($requestMethod=="POST") {
                curl_setopt($ch, CURLOPT_POST, 1);
            } elseif ($requestMethod=="PUT") {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            } elseif ($requestMethod=="DELETE") {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            }
            if ($requestParameters) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($requestParameters));
            }
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        if (strpos($link, "https")===0) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt(
            $ch,
            CURLOPT_HEADERFUNCTION,
            function ($curl, $header) use (&$headers) {
                $len = strlen($header);
                $header = explode(':', $header, 2);
                if (count($header) < 2) { // ignore invalid headers
                    return $len;
                }

                $name = strtolower(trim($header[0]));
                if (!array_key_exists($name, $headers)) {
                    $headers[$name] = [trim($header[1])];
                } else {
                    $headers[$name][] = trim($header[1]);
                }

                return $len;
            }
        );
        $result = curl_exec($ch);
        $error = curl_error($ch);
        try {
            if ($error) {
                throw new RequestException($error);
            }
            $info = curl_getinfo($ch);
            $this->response = new Response($info["http_code"], $result, $headers);
        } finally {
            curl_close($ch); // this makes connection is closed no matter what
        }
    }

    /**
     * Gets response associated to request
     *
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}
