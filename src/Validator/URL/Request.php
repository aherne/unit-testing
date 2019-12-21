<?php
namespace Lucinda\UnitTest\Validator\URL;

use Lucinda\UnitTest\Exception;

/**
 * Encapsulates a request to an URL via cURL, supporting all HTTP verbs (GET, POST, PUT, DELETE)
 */
class Request
{
    private $response;

    /**
     * Generates a request to an URL based on criteria.
     *
     * @param DataSource $dataSource
     * @throws Exception If URL called is non-responsive.
     */
    public function __construct(DataSource $dataSource)
    {
        $this->setResponse($dataSource);
    }

    /**
     * Runs request and encapsulates results into a Response object
     *
     * @param DataSource $dataSource
     * @throws Exception If URL called is non-responsive.
     */
    private function setResponse(DataSource $dataSource): void
    {
        $headers = [];
        $ch = curl_init();
        if ($dataSource->getRequestMethod()=="GET") {
            curl_setopt($ch, CURLOPT_URL, $dataSource->getURL().($dataSource->getRequestParameters()?"?".http_build_query($dataSource->getRequestParameters()):""));
        } else {
            curl_setopt($ch, CURLOPT_URL, $dataSource->getURL());
            if ($dataSource->getRequestMethod()=="POST") {
                curl_setopt($ch, CURLOPT_POST, 1);
            } elseif ($dataSource->getRequestMethod()=="PUT") {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            } elseif ($dataSource->getRequestMethod()=="DELETE") {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            }
            if ($dataSource->getRequestParameters()) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($dataSource->getRequestParameters()));
            }
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $dataSource->getRequestHeaders());
        if (strpos($dataSource->getURL(), "https")===0) {
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
                throw new Exception($error);
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
