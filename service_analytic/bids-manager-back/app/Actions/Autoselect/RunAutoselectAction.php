<?php

namespace App\Actions\Autoselect;

use App\Actions\Action;
use App\DataTransferObjects\Frontend\Autoselect\AutoselectParametersRequestData;
use App\Repositories\V2\Product\CategoryRepository;
use App\Tasks\Autoselect\CreateAutoselectParameterTask;
use App\Tasks\Autoselect\GetAutoselectResultsCountTask;
use App\Tasks\Autoselect\GetFilteredAutoselectResultsTask;
use App\Tasks\VirtualAssistant\FindKeywordsWithStatisticTask;
use App\Tasks\VirtualAssistant\PrepareAutoselectResultsDataFromVATask;
use App\Tasks\VirtualAssistant\SaveAutoselectResultsFromVATask;

/**
 * Class RunAutoselectAction
 *
 * @package App\Actions\Autoselect
 */
class RunAutoselectAction extends Action
{
    /**
     * @param AutoselectParametersRequestData $requestData
     * @return mixed
     */
    public function run(AutoselectParametersRequestData $requestData): array
    {
        $autoselectParameterData = $requestData->except('filter', 'order')->toArray();
        $autoselectParameterId = (new CreateAutoselectParameterTask)->run($autoselectParameterData);

        $VAParams = $requestData
            ->only('keyword', 'date_from', 'date_to')
            ->toArray();

        if ($requestData->categoryId) {
            $VAParams['categoryId'] = CategoryRepository::getVaCategoryId($requestData->categoryId);
        }

        $VAServiceData = (new FindKeywordsWithStatisticTask)->run($VAParams);

        if ($VAServiceData) {
            $VADataToSave = (new PrepareAutoselectResultsDataFromVATask())->run($VAServiceData);
            (new SaveAutoselectResultsFromVATask())->run($autoselectParameterId, $VADataToSave, $requestData->categoryId ?? null);
        }

        $filter = $requestData->filter ?? [];
        $order = $requestData->order ?? ['field' => 'popularity', 'direction' => 'DESC'];
        $autoselectResultList = (new GetFilteredAutoselectResultsTask)->run($autoselectParameterId, $filter, $order);
        $autoselectResultsCount = (new GetAutoselectResultsCountTask())->run($autoselectParameterId);

        return ['results' => $autoselectResultList, 'totalCount' => $autoselectResultsCount];
    }
}
