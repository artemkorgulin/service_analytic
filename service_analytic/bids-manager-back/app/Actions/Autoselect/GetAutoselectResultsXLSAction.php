<?php

namespace App\Actions\Autoselect;

use App\Actions\Action;
use App\DataTransferObjects\Frontend\Autoselect\AutoselectXLSResultsRequestData;
use App\Tasks\Autoselect\GetAutoselectResultsTask;
use App\Tasks\Autoselect\SaveAutoselectResultsToXLSTask;

/**
 * Class GetAutoselectResultsAction
 *
 * @package App\Actions\Autoselect
 */
class GetAutoselectResultsXLSAction extends Action
{
    /**
     * @param AutoselectXLSResultsRequestData $requestData
     * @return array|string[]
     */
    public function run(AutoselectXLSResultsRequestData $requestData)
    {
        $autoselectParameterId = $requestData->autoselectParameterId;
        $autoselectResultList = (new GetAutoselectResultsTask)->run($autoselectParameterId);
        $res = (new SaveAutoselectResultsToXLSTask)->run($autoselectResultList);

        return $res;
    }
}
