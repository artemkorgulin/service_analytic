<?php

namespace App\Tasks\Autoselect;

use App\Tasks\Task;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Class GetXLSKeywordUploadTemplateTask
 *
 * @package App\Tasks\Autoselect
 */
class  GetXLSKeywordUploadTemplateTask extends Task
{
    /**
     * @return array
     */
    public function run(): array
    {
        $filename = 'keyword_upload_template.xlsx';
        $filepath = 'xls/' . $filename;
        $res = Storage::url($filepath);
        if ($res) {
            return [
                'xlsLink' => request()->server('REQUEST_SCHEME').'://' . request()->server('HTTP_HOST') . '/backend' . $res,
                'xlsName' => $filename,
            ];
        } else {
            return [];
        }
    }
}
