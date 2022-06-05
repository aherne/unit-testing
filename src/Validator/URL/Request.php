<?php

namespace Lucinda\UnitTest\Validator\URL;

use Lucinda\UnitTest\Exception;

/**
 * Encapsulates a request to an URL via cURL, supporting all HTTP verbs (GET, POST, PUT, DELETE)
 */
class Request
{
    private Response $response;

    /**
     * Generates a request to an URL based on criteria.
     *
     * @param  DataSource $dataSource
     * @throws Exception If URL called is non-responsive.
     */
    public function __construct(DataSource $dataSource)
    {
        $this->setResponse($dataSource);
    }

    /**
     * Runs request and encapsulates results into a Response object
     *
     * @param  DataSource $dataSource
     * @throws Exception If URL called is non-responsive.
     */
    private function setResponse(DataSource $dataSource): void
    {
        $headers = [];
        $handle = curl_init();
        // sets basic options
        $options = $this->getBasicOptions($dataSource);
        foreach ($options as $key=>$value) {
            curl_setopt($handle, $key, $value);
        }
        // signals capture of response headers
        curl_setopt(
            $handle,
            CURLOPT_HEADERFUNCTION,
            function ($handle, $header) use (&$headers) {
                $position = strpos($header, ":");
                if ($position !== false) {
                    $name = ucwords(trim(substr($header, 0, $position)), "-");
                    $value = trim(substr($header, $position+1));
                    $headers[$name] = $value;
                }
                return strlen($header);
            }
        );
        $result = curl_exec($handle);
        $error = curl_error($handle);
        try {
            if ($error) {
                throw new Exception($error);
            }
            $info = curl_getinfo($handle);
            $this->response = new Response($info["http_code"], $result, $headers);
        } finally {
            curl_close($handle); // this makes connection is closed no matter what
        }
    }

    /**
     * Gets basic cURL options to use in request
     *
     * @param  DataSource $dataSource
     * @return array<int, mixed>
     */
    private function getBasicOptions(DataSource $dataSource): array
    {
        $options = [];
        if ($dataSource->getRequestMethod()=="GET") {
            $queryString = ($dataSource->getRequestParameters() ? "?".http_build_query($dataSource->getRequestParameters()) : "");
            $options[CURLOPT_URL] = $dataSource->getURL().$queryString;
        } else {
            $options[CURLOPT_URL] = $dataSource->getURL();
            if ($dataSource->getRequestMethod()=="POST") {
                $options[CURLOPT_POST] = 1;
            } elseif ($dataSource->getRequestMethod()=="PUT") {
                $options[CURLOPT_CUSTOMREQUEST] = "PUT";
            } elseif ($dataSource->getRequestMethod()=="DELETE") {
                $options[CURLOPT_CUSTOMREQUEST] = "DELETE";
            }
            if ($dataSource->getRequestParameters()) {
                $options[CURLOPT_POSTFIELDS] = http_build_query($dataSource->getRequestParameters());
            }
        }
        $options[CURLOPT_HTTPHEADER] = $dataSource->getRequestHeaders();
        if (str_starts_with($dataSource->getURL(), "https")) {
            $options[CURLOPT_SSL_VERIFYHOST] = 0;
            $options[CURLOPT_SSL_VERIFYPEER] = 0;
        }
        $options[CURLOPT_TIMEOUT] = 10;
        $options[CURLOPT_RETURNTRANSFER] = 1;
        return $options;
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
