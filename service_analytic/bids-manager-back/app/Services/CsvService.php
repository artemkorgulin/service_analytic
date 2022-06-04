<?php


namespace App\Services;

use finfo;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class CsvService
{

    const ZIP_PATH = 'temp_zip';
    const CSV_PATH = 'csv';
    const MAX_ROWS = 3000;


    /**
     * Extract CSV files from zip archive
     *
     * @param  mixed  $zipData
     * @param  string  $uuid
     *
     * @return array|false $filenames
     */
    public static function extractFilesFromZip(mixed $zipData, string $uuid): array|false
    {
        //save file to temp folder
        $zipName = $uuid;//date('YmdHis');
        $zipPath = sprintf('%s/%s.zip', self::ZIP_PATH, $zipName);

        Storage::put($zipPath, $zipData);

        $csvDir = self::getCsvDir($uuid);

        //extract csv from zip
        $zip         = new ZipArchive();
        $filenames   = [];
        $zipFullPath = Storage::path($zipPath);
        if ($zip->open($zipFullPath) === true) {
            $zip->extractTo($csvDir);
            $filenames[] = Storage::allFiles($csvDir);
            $zip->close();
        } else {
            echo('Ошибка открытия ZIP файла '.$zipPath);

            return false;
        }

        return $filenames;
    }


    /**
     * Save csv to temp folder
     *
     * @param  mixed  $fileData
     * @param  string  $uuid
     * @param  string  $filename
     *
     * @return string $filename
     */
    public static function saveTemporaryCsv(mixed $fileData, string $uuid, string $filename): string
    {
        $csvDir = self::getCsvDir($uuid);

        $filepath = $csvDir.$filename.'.csv';
        Storage::put($filepath, $fileData);

        return $filepath;
    }


    /**
     * Parse report csv file
     *
     * @param  string  $filename
     *
     * @return array|bool
     */
    public static function parseCsv(string $filename): bool|array
    {
        $row        = 1;
        $report     = [];
        $campaignId = 0;
        if (($file = fopen($filename, "r")) !== false) {
            while (($data = fgetcsv($file, self::MAX_ROWS, ";")) !== false) {
                // Заголовок
                if ($row == 1) {
                    $campaignId = explode(" ", explode(",", $data[1])[0])[3];
                }
                // Данные
                if ($row >= 3) {
                    $report[] = $data;
                }
                $row++;
            }
            fclose($file);

            array_pop($report);

            return [$report, $campaignId];
        } else {
            echo 'failed open file '.$filename;

            return false;
        }
    }


    /**
     * Check if response is zip archive or not
     *
     * @param $fileData
     *
     * @return bool
     */
    public static function fileContentIsArchive($fileData = null): bool
    {
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($fileData);

        return $mimeType === 'application/zip';
    }


    /**
     * @param  string|null  $uuid
     *
     * @return string
     */
    private static function getCsvDir(string $uuid = null): string
    {
        $dir = sprintf('%s/%s/', self::CSV_PATH, date('Y-m-d'));
        if ($uuid) {
            $dir .= $uuid.'/';
        }

        return Storage::path($dir);
    }

}
