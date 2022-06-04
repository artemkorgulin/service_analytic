<?php

namespace Tests\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

trait TestsGuzzleRequests
{

    private $client;

    private $responses = [];


    /**
     * Get Guzzle mock client
     *
     * @return \GuzzleHttp\Client
     */
    protected function getClient()
    {
        if ($this->client) {
            return $this->client;
        }

        return new Client([
            'handler' => function (Request $request) {
                $path = $request->getUri()->getPath();
                if (array_key_exists($path, $this->responses)) {
                    return $this->responses[$path];
                }

                return new Response(404);
            }
        ]);
    }


    /**
     * Add response to the GuzzleHttp mock client
     *
     * @param  string  $path
     * @param  int  $status
     * @param  array  $headers
     * @param  array|string  $body
     *
     * @return void
     */
    protected function setResponse(string $path, int $status = 200, array $headers = [], array|string $body = [])
    {
        $this->responses[$path] = $this->getResponseObject(compact('status', 'headers', 'body'));
    }


    /**
     * Create Guzzle Response object
     * from array
     *
     * @param  array  $responseData
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    protected function getResponseObject(array $responseData = []): Response
    {
        if (array_intersect(['headers', 'body', 'status'], array_keys($responseData))) {
            $body = null;
            if (!empty($responseData['body'])) {
                $body = $responseData['body'];
            }

            if (is_array($body)) {
                $body = json_encode($body);
            }

            return new Response(
                status: $responseData['status'] ?? 200,
                headers: $responseData['headers'] ?? null,
                body: $body,
            );
        }

        return new Response(body: json_encode($responseData));
    }


    /**
     * Add responses to the GuzzleHttp mock client
     *
     * @param  array  $responsesData
     *
     * @return void
     */
    protected function setResponses(array $responsesData)
    {
        if (!empty($responsesData)) {
            foreach ($responsesData as $path => $responseData) {
                $this->responses[$path] = $this->getResponseObject($responseData);
            }
        }
    }


    /**
     * Empty responses array
     *
     * @return void
     */
    protected function flushResponses()
    {
        $this->responses = [];
    }
}
