<?php

declare(strict_types=1);


namespace Saniori\Cavaleria;

class Client
{

    private false|string $accessToken = false;
    private false|string $basicAuthCredentials = false;

    /**
     * @param Api $api
     */
    public function __construct(private Api $api)
    {
    }

    /**
     * @return bool
     */
    public function isAuthorized(): bool
    {

        return (bool)$this->accessToken || (bool)$this->basicAuthCredentials;

    }

    /**
     * @return false|string
     */
    public function getAccessToken(): false|string
    {

        return $this->accessToken;

    }

    /**
     * @return false|string
     */
    public function getBasicAuthCredentials(): false|string
    {

        return $this->basicAuthCredentials;

    }

    /**
     * Auth request
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

        return $this->api->request($method, $endpoint, body: $body, headers: array_merge([
            "Authorization: Basic {$this->getBasicAuthCredentials()}",
            "Accept-Encoding: gzip",
            "Content-Type: application/json"
        ], $headers), options: $options);

    }

    /**
     * @param string $login
     * @param string $password
     * @return false|string
     */
    public function auth(string $login, string $password): false|string
    {

        $this->basicAuthCredentials = base64_encode("$login:$password");

        try {

            $response = $this->request("POST", "/security/token", options: ["noErrors" => true]);

            $token = @$response["access_token"] ?? false;

        } catch (\Throwable $e) {

            return false;

        }

        $this->accessToken = $token;

        return $token;

    }

    /**
     * @return array|false
     */
    public function getOrganization(): false|array
    {

        try {

            $response = $this->request("GET", "/entity/organization", options: ["noErrors" => true]);

        } catch (\Throwable $e) {

            return false;

        }

        return $response;

    }

    /**
     * @param string $name
     * @return bool|array
     */
    public function createCounterPartyByName(string $name): false|array
    {

        try {

            $response = $this->request("POST", "/entity/counterparty", body: ["name" => $name], options: ["noErrors" => true]);

        } catch (\Throwable $e) {

            return false;

        }

        return $response;

    }

    /**
     * @param mixed $OrganizationMetadata
     * @param mixed $CounterPartyMetadata
     * @return array|false
     */
    public function newCustomerOrder(mixed $OrganizationMetadata, mixed $CounterPartyMetadata): false|array
    {

        try {

            $response = $this->request("POST", "/entity/customerorder", body: [
                "organization" => ["meta" => $OrganizationMetadata],
                "agent" => ["meta" => $CounterPartyMetadata]
            ], options: ["noErrors" => true]);

        } catch (\Throwable $e) {

            return false;

        }

        return $response;

    }

    /**
     * @param array $productMetadata
     * @param mixed $customerOrderID
     * @param array $options
     * @return array|false
     */
    public function addPositionForCustomerOrder(array $productMetadata, mixed $customerOrderID, array $options = []): false|array
    {

        $body = $options;
        $body["assortment"] = ["meta" => $productMetadata];

        try {

            $response = $this->request("POST", "/entity/customerorder/$customerOrderID/positions", body: $body, options: ["noErrors" => true]);

        } catch (\Throwable $e) {

            return false;

        }

        return $response;

    }

    /**
     * @param array $OrganizationMetadata
     * @param array $CounterPartyMetadata
     * @param array $StoreMetadata
     * @return bool|array
     */
    public function newDemand(array $OrganizationMetadata, array $CounterPartyMetadata, array $StoreMetadata): bool|array
    {

        try {

            $response = $this->request("POST", "/entity/demand", body: [
                "organization" => ["meta" => $OrganizationMetadata],
                "agent" => ["meta" => $CounterPartyMetadata],
                "store" => ["meta" => $StoreMetadata]
            ], options: ["noErrors" => true]);

        } catch (\Throwable $e) {

            return false;

        }

        return $response;

    }

    /**
     * @param string $productID
     * @return bool|array
     */
    public function getProduct(string $productID): bool|array
    {

        try {

            $response = $this->request("GET", "/entity/product/$productID", options: ["noErrors" => true]);

        } catch (\Throwable $e) {

            return false;

        }

        return $response;

    }

}