<?php

namespace App\Http\Controllers\Api\Wildberries;

use App\Http\Requests\AccessValidatorRequest;
use App\Services\Wildberries\Client;
use Illuminate\Http\JsonResponse;

class AccessValidatorController extends CommonController
{
    /** @var int */
    private int $repeats = 3;

    /**
     * Check access with api_key
     *
     * @param AccessValidatorRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function validateAccessData(AccessValidatorRequest $request): JsonResponse
    {
        return $this->result(optional($this->client($request))->getDirectoryColors());
    }

    /**
     * Check access with api_key and client_id
     *
     * @param AccessValidatorRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function validateAccessDataForClientId(AccessValidatorRequest $request): JsonResponse
    {
        $response = optional($this->client($request))->getCardList();
        return $this->result($response[0]['id'] ?? null);
    }

    /**
     * Get WB client for requests
     *
     * @param AccessValidatorRequest $request
     * @return Client
     * @throws \Exception
     */
    private function client(AccessValidatorRequest $request): Client
    {
        return new Client($request->client_id, $request->api_key, $this->repeats, false);
    }

    /**
     * Return result
     *
     * @param $response
     * @return JsonResponse
     */
    private function result($response): JsonResponse
    {
        if (empty($response)) {
            return response()->api_fail('Access denied', [], 403);
        }
        return response()->api_success('Access applied');
    }
}
