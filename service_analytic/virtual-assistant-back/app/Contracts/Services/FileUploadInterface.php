<?php

namespace App\Contracts\Services;

interface FileUploadInterface
{
    /**
     * @param  string  $filepath
     * @return array
     */
    public function getFileDataToArray(string $filepath): array;
}
