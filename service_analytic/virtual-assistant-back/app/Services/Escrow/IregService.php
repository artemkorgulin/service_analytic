<?php

namespace App\Services\Escrow;

use App\Services\Escrow\Interfaces\IregServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class IregService implements IregServiceInterface
{
    private $url;
    private $apiKey;

    const WILDBERRIES_URL = "https://www.wildberries.ru";

    public function __construct()
    {
        $this->url = config('env.ireg_api_url');
        $this->apiKey = config('env.ireg_api_key');
    }

    /**
     * Make request to escrow file
     * @param $filePath
     * @return mixed
     */
    public function fileStore($filePath, $product, $escrowRequest): mixed
    {
        if (class_basename($product) == 'OzProduct') {
            $productUrl = $product->url;
            $productMarketplaceNumber = $product->sku;
        } else {
            $productUrl = self::WILDBERRIES_URL . "/catalog/$escrowRequest->nmid/detail.aspx";
            $productMarketplaceNumber = $escrowRequest->nmid;
        }

        $postFields = [
            'autor_ru' => $escrowRequest->full_name,
            'autor_en' => Str::of($escrowRequest->full_name)->slug(' '),
            'owner_ru' => $escrowRequest->copyright_holder,
            'owner_en' => Str::of($escrowRequest->copyright_holder)->slug(' '),
            'email' => $escrowRequest->email,
            'number' => str_pad($productMarketplaceNumber, 9, '0', STR_PAD_LEFT),
            'link' => $productUrl,
        ];

        $json = Http::withToken($this->apiKey)
            ->attach('file', fopen($filePath, 'r'))
            ->post($this->url . '/file/store', $postFields);
        return json_decode($json);
    }
}
