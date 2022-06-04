<?php

namespace App\Tasks\Autoselect;

use App\Models\AutoselectParameter;
use App\Tasks\Task;

/**
 * Class CreateAutoselectParameterTask
 *
 * @package App\Tasks\Autoselect
 */
class CreateAutoselectParameterTask extends Task
{
    /**
     * @param array $autoselectParametersData
     * @return int
     */
    public function run(array $autoselectParametersData): int
    {
        $autoselectParameters = new AutoselectParameter($autoselectParametersData);
        $autoselectParameters->save();
        return $autoselectParameters->id;
    }
}
