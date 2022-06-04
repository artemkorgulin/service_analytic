<?php


namespace App\Classes\Parser;


use App\Models\WbProduct;
use GuzzleHttp\Client;
use stdClass;

class Api
{
    protected $curl;

    public function __construct()
    {
        $this->curl = new Client([
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]
        ]);
    }

    private $site = 'http://94.228.127.130:3011';

    /**
     * @param $categoryName
     * @return int|null
     */
    public function getWbParseCategory($categoryName): ?int
    {
        $wbProducts = WbProduct::where('object', $categoryName)->take(10)->get();

        foreach ($wbProducts as $wbProduct) {
            $productParsing = $this->getParsingProduct($wbProduct->nmid);
            if ($productParsing) {
                return $productParsing->category[0]->web_id;
            }
        }

        return null;
    }

    /**
     * @param $webId
     * @return array|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getWbTopProducts($webId): ?array
    {
        return json_decode($this->curl->request('GET',
            $this->site.'/api/top/p?category='.$webId.'&limit=100')->getBody())->top;
    }

    /**
     * @param $id
     * @return stdClass|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getParsingProduct($id): ?stdClass
    {
        return json_decode($this->curl->request('GET',
            $this->site.'/api/goods/'.$id)->getBody())->data;
    }

    /**
     * @param $webId
     * @param $subjectId
     * @return array|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCategoryTree($webId, $subjectId): ?array
    {
        return json_decode($this->curl->request('GET',
            $this->site.'/api/category/web_id/'.$webId.'/subject_id/'.$subjectId)->getBody())->category;
    }
}
