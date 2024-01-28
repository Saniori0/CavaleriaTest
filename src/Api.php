<?php

declare(strict_types=1);

namespace Saniori\Cavaleria;

class Api
{

    /**
     * @param string $host
     */
    public function __construct(private string $host)
    {

        if (!filter_var($host, FILTER_VALIDATE_URL)) throw new \InvalidArgumentException("Host not URL");

        $this->host = rtrim($this->host, "/");

    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param array $body
     * @param array $headers
     * @param array $options
     * @return array
     * @throws \Exception
     */
    public function request(string $method, string $endpoint, array $body = [], array $headers = [], array $options = []): array
    {

        $httpMethods = ["POST", "GET", "PUT", "DELETE", "PATCH"];

        if (!in_array($method, $httpMethods)) throw new \BadMethodCallException("Method not exist");

        $curlRequest = curl_init("{$this->getHost()}$endpoint");
        curl_setopt($curlRequest, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlRequest, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curlRequest, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curlRequest, CURLOPT_ENCODING, 'UTF-8');
        curl_setopt($curlRequest, CURLOPT_POSTFIELDS, json_encode($body));

        $responseData = curl_exec($curlRequest);

        if (curl_error($curlRequest)) throw new \Exception("Curl error");

        curl_close($curlRequest);

        $responseDataDecoded = json_decode($responseData, true);

        if (@$options["noErrors"] && isset($responseDataDecoded["errors"])) throw new \Exception("Response error");

        return $responseDataDecoded;

    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

}