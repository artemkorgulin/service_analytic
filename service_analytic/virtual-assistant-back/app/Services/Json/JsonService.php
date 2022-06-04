<?php

namespace App\Services\Json;

use App\Services\Interfaces\Json\JsonServiceInterface;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use JsonMachine\Items;

class JsonService implements JsonServiceInterface
{

    /**
     * Save json stream to jsom file in filesystem
     * @param $filename
     * @param $stream
     * @return string|null
     */
    public function saveJsonToFile($filename, $stream): ?string
    {
        if (empty($stream)) {
            return null;
        }

        try {
            Storage::disk('json')->delete("$filename.json");
        } catch (\Exception $exception) {
            self::removeJson($filename);
            return null;
        }
        Storage::disk('json')->put("$filename.json", '');
        $path = Storage::disk('json')->path("$filename.json");

        try {
            while (!$stream->eof()) {
                $chunk = $stream->read(8192);
                File::append($path, $chunk);
            }
        } catch (\Exception $exception) {
            self::removeJson($filename);
            return null;
        }
        return $path;
    }

    /**
     * Parse json file and return items
     * @param $filename
     * @return Items|null
     */
    public function getJsonItems($filename, $pointer = ''): ?Items
    {
        $path = Storage::disk('json')->path("$filename.json");
        try {
            $items = Items::fromFile($path, ['pointer' => $pointer, 'debug' => true]);
        } catch (\Exception $exception) {
            self::removeJson($filename);
            return null;
        }
        return $items;
    }

    /**
     * Delete json file after actions
     * @param $filename
     * @return void
     */
    public static function removeJson($filename)
    {
        try {
            Storage::disk('json')->delete("$filename.json");
        } catch (\Exception $exception) {
            return;
        }
    }
}
