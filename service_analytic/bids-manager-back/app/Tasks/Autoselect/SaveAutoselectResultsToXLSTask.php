<?php

namespace App\Tasks\Autoselect;

use App\Tasks\Task;
use App\Exports\AutoselectResultsExport;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Class SaveAutoselectResultsToXLSTask
 *
 * @package App\Tasks\Autoselect
 */
class SaveAutoselectResultsToXLSTask extends Task
{
    /**
     * @param Collection $autoselectResultsData
     * @return array|string[]
     */
    public function run(Collection $autoselectResultsData)
    {
        $data = $autoselectResultsData;
        $ext = \Maatwebsite\Excel\Excel::XLSX;
        $filename = 'autoselect_results.xlsx';
        $filepath = 'xls/autoselect/' . date('Y-m-d') . '/' . date_timestamp_get(now()) . '_' . $filename;
        $res = Excel::store(new AutoselectResultsExport($data), $filepath, 'public', $ext);
        if ($res) {
            $url = Storage::url($filepath);
            return [
                'xlsLink' => $url,
                'xlsName' => $filename,
            ];
        } else {
            return [];
        }
    }
}
