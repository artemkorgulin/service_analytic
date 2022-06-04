<?php

namespace App\Services\File;

use App\Contracts\Services\FileUploadInterface;
use Illuminate\Support\Facades\Storage;

class CsvFileUploadService implements FileUploadInterface
{
    /**
     * {@inheritDoc}
     * @see FileUploadInterface::getFileDataToArray()
     */
    public function getFileDataToArray(string $filepath): array
    {
        $file = Storage::disk('local')->path($filepath);
        $csv = array_map('str_getcsv', file($file));

        array_walk($csv, function (&$a) use ($csv) {
            $a = array_combine($csv[0], $a);
        });

        // remove column header
        array_shift($csv);

        return $csv;
    }
}
