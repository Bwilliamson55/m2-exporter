<?php

declare(strict_types=1);

namespace Bwilliamson\Exporter\Service;

use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Webapi\Rest\Request;

/**
 * Class RestService
 *
 * A simple guzzle implemented rest client
 */
class RestService
{
    private ResponseFactory $responseFactory;
    private ClientFactory $clientFactory;
    public Client $client;

    public function __construct(
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory
    ) {
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Example usage
     */
    public function execute(): void
    {
        $repositoryName = 'magento/magento2';
        $response = $this->doRequest(
            'https://api.github.com/',
            'repos/' . $repositoryName,
            ['headers' => [
                'Authorization' => 'Bearer ' . 'my_token',
                'Content-Type' => 'Application/Json'
            ]], //see GuzzleHttp\RequestOptions for more options
            'post'
        );
        $status = $response->getStatusCode(); // 2/3/4/500 codes
        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents(); // responseContent is in JSON
        // Add your logic using $responseContent
    }

    /**
     * Do API request with provided params
     *
     * @param string $apiRequestUri
     * @param string $uriEndpoint
     * @param array $params
     * @param string $requestMethod
     *
     * @return Response
     */
    private function doRequest(
        string $apiRequestUri,
        string $uriEndpoint,
        array $params = [],
        string $requestMethod = Request::HTTP_METHOD_GET
    ): Response {
        $client = $this->clientFactory->create(['config' => [
            'base_uri' => $apiRequestUri
        ]]);
        try {
            $response = $client->request(
                $requestMethod,
                $uriEndpoint,
                $params
            );
        } catch (GuzzleException $exception) {
            /** @var Response $response */
            $response = $this->responseFactory->create([
                'status' => $exception->getCode(),
                'reason' => $exception->getMessage()
            ]);
        }

        return $response;
    }
}
