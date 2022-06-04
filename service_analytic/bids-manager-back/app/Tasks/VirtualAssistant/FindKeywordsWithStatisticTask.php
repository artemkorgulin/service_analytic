<?php

namespace App\Tasks\VirtualAssistant;

use App\Services\VirtualAssistantService;
use App\Tasks\Task;

/**
 * Class FindKeywordsWithStatistic
 *
 * @package App\Tasks\VirtualAssistant
 */
class FindKeywordsWithStatisticTask extends Task
{
    /**
     * @param array $VAParams
     * @return false|mixed|null
     */
    public function run(array $VAParams)
    {
        $VAService = VirtualAssistantService::connect();
        $VAResponse = $VAService->findKeywordsWithStatistic($VAParams);
        return $VAResponse;
    }
}
