<?php

namespace App\Services\Interfaces\Json;

use JsonMachine\Items;

interface JsonServiceInterface
{
    public function saveJsonToFile($filename, $stream): ?string;

    public function getJsonItems($filename, $pointer = ''): ?Items;

    public static function removeJson($filename);
}
