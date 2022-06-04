<?php

namespace App\Tasks\VirtualAssistant;

use App\Models\AutoselectResult;
use App\Tasks\Task;

/**
 * Class SaveAutoselectResultsFromVATask
 *
 * @package App\Tasks\VirtualAssistant
 */
class SaveAutoselectResultsFromVATask extends Task
{
    /**
     * @param int   $autoselectParametersId
     * @param array $VAServiceData
     * @param       $categoryId
     */
    public function run(int $autoselectParametersId, array $VAServiceData, $categoryId)
    {
        foreach ($VAServiceData as $requestId => $keywordData) {
            $autoselectResult = new AutoselectResult($keywordData);
            $autoselectResult->autoselect_parameter_id = $autoselectParametersId;
            $autoselectResult->va_request_id = $requestId;
            $autoselectResult->category_id = $categoryId;
            $autoselectResult->save();
        }
    }
}
