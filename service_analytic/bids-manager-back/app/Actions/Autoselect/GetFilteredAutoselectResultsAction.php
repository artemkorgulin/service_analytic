<?php

namespace App\Actions\Autoselect;

use App\Actions\Action;
use App\Tasks\Autoselect\GetFilteredAutoselectResultsTask;
use App\DataTransferObjects\Frontend\Autoselect\AutoselectResultsRequestData;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class GetFilteredAutoselectResultsAction
 *
 * @package App\Actions\Autoselect
 */
class GetFilteredAutoselectResultsAction extends Action
{
    /**
     * @param AutoselectResultsRequestData $requestData
     * @return Collection
     */
    public function run(AutoselectResultsRequestData $requestData): Collection
    {
        $autoselectParameterId = $requestData->autoselectParameterId;
        $filter = $requestData->filter ?? [];
        $order = $requestData->order ?? ['field' => 'id'];
        $autoselectResultList = (new GetFilteredAutoselectResultsTask)->run($autoselectParameterId, $filter, $order);
        return $autoselectResultList;
    }
}
