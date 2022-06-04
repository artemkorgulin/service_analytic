<?php

namespace App\Actions\Autoselect;

use App\Actions\Action;
use App\Tasks\Autoselect\GetXLSKeywordUploadTemplateTask;

/**
 * Class GetXLSKeywordUploadTemplateAction
 *
 * @package App\Actions\Autoselect
 */
class GetXLSKeywordUploadTemplateAction extends Action
{
    /**
     * @return array
     */
    public function run(): array
    {
        $res = (new GetXLSKeywordUploadTemplateTask)->run();
        return $res;
    }
}
